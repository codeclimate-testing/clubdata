# PHP Form Example

Include the forms class directly or use vendor/autoload.php if you install
it with composer.

    require("forms.php");

Create a form object.

    $form=new form_class;

Define the name of the form to be used for example in Javascript
validation code generated by the class.

    $form->NAME="subscription_form";

Use the GET method if you want to see the submitted values in the form
processing URL, or POST otherwise.

    $form->METHOD="POST";

Make the form be displayed and also processed by this script.

    $form->ACTION="";

Specify a debug output function you really want to output any
programming errors.

    $form->debug="trigger_error";

Define a warning message to display by Javascript code when the user
attempts to submit the this form again from the same page.

    $form->ResubmitConfirmMessage=
        "Are you sure you want to submit this form again?";

Output previously set password values.

    $form->OutputPasswordValues=1;

Output multiple select options values separated by line breaks

    $form->OptionsSeparator="<br />\n";

Output all validation errors at once.

    $form->ShowAllErrors=1;

CSS class to apply to all invalid inputs.
Set to a non-empty string to specify the invalid input CSS class

    $form->InvalidCLASS='invalid';

Style to apply to all invalid inputs when you just want to override a few
style attributes, instead of replacing the CSS class.
Set to a non-empty string to specify the invalid input style attributes

    $form->InvalidSTYLE='background-color: #ffcccc; border-color: #000080';

Text to prepend and append to the validation error messages.

    $form->ErrorMessagePrefix="- ";
    $form->ErrorMessageSuffix="";

Define the form field properties even if they may not be displayed.

    $form->AddInput(array(
        "TYPE"=>"text",
        "NAME"=>"email",
        "ID"=>"email",
        "MAXLENGTH"=>100,
        "Capitalization"=>"lowercase",
        "ValidateAsEmail"=>1,
        "ValidationErrorMessage"=>"It was not specified a valid e-mail address.",
        "LABEL"=>"<u>E</u>-mail address",
        "ACCESSKEY"=>"E"
    ));

    $form->AddInput(array(
        "TYPE"=>"submit",
        "ID"=>"button_subscribe",
        "VALUE"=>"Submit subscription",
        "ACCESSKEY"=>"u"
    ));

# PHP Form Validation

Load form input values eventually from the submitted form.

    $form->LoadInputValues($form->WasSubmitted("doit"));

Empty the array that will list the values with invalid field after
validation.

    $verify=array();

Check if the was submitted as opposed to being displayed for the first
time.

    if($form->WasSubmitted())
    {

Therefore we need to validate the submitted form values.

        if(($error_message=$form->Validate($verify))=="")
        {

It's valid, set the $doit flag variable to 1 to tell the form is ready to
processed.

            $doit=1;

        }
        else
        {

It's invalid, set the $doit flag to 0 and encode the returned error
message to escape any HTML special characters.

            $doit=0;
            $error_message=nl2br(HtmlSpecialChars($error_message));
        }
    }
    else
    {

The form is being displayed for the first time, so it is not ready to be processed
and there is no error message to display.

        $error_message="";
        $doit=0;
    }

    if($doit)
    {

The form is ready to be processed, just output it again as read only to
display the submitted values.  A real form processing script usually may
do something else like storing the form values in a database.

      $form->ReadOnly=1;
    }

If the form was not submitted or was not valid, make the page ONLOAD event
give the focus to the first form field or the first invalid field.

    if(!$doit)
    {
        if(strlen($error_message))
        {

If there is at least one field with invalid values, get the name of the
first field in error to make it get the input focus when the page is
loaded.

            Reset($verify);
            $focus=Key($verify);
        }
        else
        {

Make the email field get the input focus when the page is loaded if there
was no previous validation error.

            $focus='email';
        }

Connect the form to the field to get the input focus when the page loads.

        $form->ConnectFormToInput($focus, 'ONLOAD', 'Focus', array());
    }

# PHP HTML Form Example Output

    $onload = HtmlSpecialChars($form->PageLoad());

    ?><!DOCTYPE>
    <html>
    <head>
    <title>Test for Manuel Lemos' PHP form class</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <style type="text/css"><!--
        .invalid { border-color: #ff0000; background-color: #ffcccc; }
    // --></style>
    </head>
    <body onload="<?php echo $onload; ?>" bgcolor="#cccccc">
    <h1>Test for Manuel Lemos' PHP form class</h1>
    <hr />

Compose the form output by including a HTML form template with PHP code
interleaaved with calls to insert form input field parts in the layout HTML.

    $form->StartLayoutCapture();
    $title="Form class test";
    $body_template="form_body.html.php";
    require("templates/form_frame.html.php");
    $form->EndLayoutCapture();

Output the form using the function named Output.

    $form->DisplayOutput();

    ?></body>
    </html>