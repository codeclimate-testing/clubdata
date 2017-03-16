<?php
/*
 * sendmail_message.php
 *
 * @(#) $Header: /cvsroot/clubdata/Clubdata2/include/mimemessage/sendmail_message.php,v 1.1.1.1 2006/02/04 20:43:37 domes Exp $
 *
 *
 */

/*
 {metadocument}<?xml version="1.0" encoding="ISO-8859-1"?>
 <class>

 <package>net.manuellemos.mimemessage</package>

 <name>sendmail_message_class</name>
 <version>@(#) $Id: sendmail_message.php,v 1.1.1.1 2006/02/04 20:43:37 domes Exp $</version>
 <copyright>Copyright � (C) Manuel Lemos 1999-2004</copyright>
 <title>MIME E-mail message composing and sending using Sendmail</title>
 <author>Manuel Lemos</author>
 <authoraddress>mlemos-at-acm.org</authoraddress>

 <documentation>
 <idiom>en</idiom>
 <purpose>Implement an alternative message delivery method using
 <link>
 <data>Sendmail</data>
 <url>http://www.sendmail.org/</url>
 </link> MTA (Mail Transfer Agent).</purpose>
 <usage>This class should be used exactly the same way as the base
 class for composing and sending messages. Just create a new object of
 this class as follows and set only the necessary variables to
 configure details of delivery using Sendmail.<paragraphbreak />
 <tt>require('email_message.php');<br />
 require('sendmail_message.php');<br />
 <br />
 $message_object = new sendmail_message_class;<br /></tt><paragraphbreak />
 <b>- Tuning the delivery mode for mass mailing</b><paragraphbreak />
 Sendmail supports several message delivery modes. In many
 installations the default is to attempt to deliver the message right
 away when the message is handed by the applications to
 Sendmail.<paragraphbreak />
 This may be an inconvenient because it makes PHP scripts wait for the
 message to be delivered to the destination SMTP server. If the SMTP
 connection with that server is slow, it may stall the delivery for a
 long while.<paragraphbreak />
 Under Unix/Linux, PHP defaults to using Sendmail or equivalent to
 deliver messages sent with the <tt>mail()</tt> function. Some people
 assume that it is faster to queue messages by relaying to an
 intermediate SMTP server than to use the <tt>mail()</tt> function
 that uses Sendmail. This is not accurate.<paragraphbreak />
 Sendmail supports other message delivery modes that can be used for
 much faster message queueing. These modes are more recommended for
 mass mailing. Adjust the value of the
 <variablelink>delivery_mode</variablelink> variable to improve the
 message queueing rate if you want to use this class for mass mailing.
 </usage>
 </documentation>

 {/metadocument}
 */

define("SENDMAIL_DELIVERY_DEFAULT",     "");
define("SENDMAIL_DELIVERY_INTERACTIVE", "i");
define("SENDMAIL_DELIVERY_BACKGROUND",  "b");
define("SENDMAIL_DELIVERY_QUEUE",       "q");
define("SENDMAIL_DELIVERY_DEFERRED",    "d");

class sendmail_message_class extends email_message_class
{
	/* Private variables */

	var $line_break="\n";

	/* Public variables */

	/*
	 {metadocument}
	 <variable>
		<name>sendmail_path</name>
		<type>STRING</type>
		<value>/usr/lib/sendmail</value>
		<documentation>
		<purpose>Specifying the path of the <tt>sendmail</tt> executable
		program.</purpose>
		<usage>The original default path of the <tt>sendmail</tt> used to be
		<tt>/usr/lib/sendmail</tt>. However, currently it is usually
		located in <tt>/usr/sbin/sendmail</tt> having a symbolic link
		pointing to that path from
		<tt>/usr/lib/sendmail</tt>.<paragraphbreak />
		If this symbolic link does not exist or the <tt>sendmail</tt> is
		different in your installation, you need to change this
		variable.</usage>
		</documentation>
		</variable>
		{/metadocument}
		*/
	var $sendmail_path="/usr/lib/sendmail";

	/*
	 {metadocument}
	 <variable>
		<name>delivery_mode</name>
		<type>STRING</type>
		<value></value>
		<documentation>
		<purpose>Specify the Sendmail message delivery mode.</purpose>
		<usage>Sendmail supports several different delivery
		modes:<paragraphbreak />
		SENDMAIL_DELIVERY_DEFAULT -
		<tt><stringvalue></stringvalue></tt><paragraphbreak />
		Does not override the default mode.<paragraphbreak />
		SENDMAIL_DELIVERY_INTERACTIVE -
		<tt><stringvalue>i</stringvalue></tt><paragraphbreak />
		Attempt to send the messages synchronously to the recipient's SMTP
		server and only returns when it succeeds or fails. This is usually
		the default mode. It stalls the delivery of messages but it may be
		safer to preserve disk space because the successfully delivered
		messages are not stored.<paragraphbreak />
		SENDMAIL_DELIVERY_BACKGROUND -
		<tt><stringvalue>b</stringvalue></tt><paragraphbreak />
		Creates a background process that attempts to deliver the message
		and returns immediately. This mode is recommended when you want
		to send a few messages as soon as possible. It is not recommended
		for sending messages to many recipients as it may consume too much
		memory and CPU that result from creating excessive background
		processes.<paragraphbreak />
		SENDMAIL_DELIVERY_QUEUE -
		<tt><stringvalue>q</stringvalue></tt><paragraphbreak />
		Just drop the message in the queue and leave it there until next
		time the queue is run. It is recommended for deliverying messages
		to many recipients as long as there is enough disk space to store
		all the messages in the queue.<paragraphbreak />
		SENDMAIL_DELIVERY_DEFERRED -
		<tt><stringvalue>d</stringvalue></tt><paragraphbreak />
		The same as the queue mode except for a few verifications that are
		skipped.<paragraphbreak />
		</usage>
		</documentation>
		</variable>
		{/metadocument}
		*/
	var $delivery_mode=SENDMAIL_DELIVERY_DEFAULT;

	/*
	 {metadocument}
	 <variable>
		<name>sendmail_arguments</name>
		<type>STRING</type>
		<value></value>
		<documentation>
		<purpose>Specify additional <tt>sendmail</tt> program arguments.</purpose>
		<usage>Use this to to pass additional arguments that are not
		supported by this class.</usage>
		</documentation>
		</variable>
		{/metadocument}
		*/
	var $sendmail_arguments="";

	/*
	 {metadocument}
	 <variable>
		<name>mailer_delivery</name>
		<value>sendmail $Revision: 1.1.1.1 $</value>
		<documentation>
		<purpose>Specify the text that is used to identify the mail
		delivery class or sub-class. This text is appended to the
		<tt>X-Mailer</tt> header text defined by the
		mailer variable.</purpose>
		<usage>Do not change this variable.</usage>
		</documentation>
		</variable>
		{/metadocument}
		*/
	var $mailer_delivery='sendmail $Revision: 1.1.1.1 $';

	Function SendMail($to,$subject,$body,$headers,$return_path)
	{
		$command=$this->sendmail_path." -t";
		switch($this->delivery_mode)
		{
			case SENDMAIL_DELIVERY_DEFAULT:
			case SENDMAIL_DELIVERY_INTERACTIVE:
			case SENDMAIL_DELIVERY_BACKGROUND:
			case SENDMAIL_DELIVERY_QUEUE:
			case SENDMAIL_DELIVERY_DEFERRED:
				break;
			default:
				return($this->OutputError("it was specified an unknown sendmail delivery mode"));
		}
		if($this->delivery_mode!=SENDMAIL_DELIVERY_DEFAULT)
		$command.=" -O DeliveryMode=".$this->delivery_mode;
		if(strlen($return_path))
		$command.=" -f '".ereg_replace("'", "'\\''",$return_path)."'";
		if(strlen($this->sendmail_arguments))
		$command.=" ".$this->sendmail_arguments;
		if(!($pipe=@popen($command,"w")))
		return($this->OutputPHPError("it was not possible to open sendmail input pipe", $php_errormsg));
		if(strlen($headers))
		$headers.="\n";
		if(!@fputs($pipe,"To: ".$to."\nSubject: ".$subject."\n".$headers."\n")
		|| !@fputs($pipe,$body)
		|| !@fflush($pipe))
		return($this->OutputPHPError("it was not possible to write sendmail input pipe", $php_errormsg));
		pclose($pipe);
		return("");
	}
};

/*

{metadocument}
</class>
{/metadocument}

*/

?>