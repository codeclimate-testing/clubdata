<?php
/**
 * @package Clubdata
 * @subpackage General
 * @author Franz Domes <franz.domes@gmx.de>
 * @version $Revision: 1.3 $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright Copyright (c) 2009, Franz Domes
 */

/**
 *
 */
require_once((defined("ADODB_DIR") ? (ADODB_DIR) : '') . "adodb.inc.php");
require_once((defined("ADODB_DIR") ? (ADODB_DIR) : '') . "drivers/adodb-mysqli.inc.php");

/**
 * @package Clubdata
 */
class clubdata_mysqli extends ADODB_mysqli{
  var $rsPrefix = 'clubdata_rs_';

  var $_tablePrefix = '';

  function clubdata_mysqli() {
  	 
    // Save table prefix: This is a constant set in configuration.php
    $this->_tablePrefix = DB_TABLEPREFIX;

    ADODB_mysqli::ADODB_mysqli();
//     $this->debug = true;
  }


  function Execute($sql,$inputarr=false)
  {
    if (function_exists('debug') )
      debug('CDDB',"[clubdata_mysqli: Execute]: SQL before: $sql");

    $sql = clubdata_mysqli::replaceDBprefix($sql, $this->_tablePrefix);

    if (function_exists('debug') )
      debug('CDDB',"[clubdata_mysqli: Execute]: SQL after: $sql");

    return parent::Execute($sql,$inputarr);
  }

  function GetRow($sql,$inputarr=false)
  {
    if (function_exists('debug') )
      debug('CDDB',"[clubdata_mysqli: GetRow]: SQL before: $sql");

    $sql = clubdata_mysqli::replaceDBprefix($sql, $this->_tablePrefix);

    if (function_exists('debug') )
      debug('CDDB',"[clubdata_mysqli: GetRow]: SQL after: $sql");

    return parent::GetRow($sql,$inputarr);
  }

  function GetOne($sql,$inputarr=false)
  {
    if (function_exists('debug') )
      debug('CDDB',"[clubdata_mysqli: GetOne]: SQL before: $sql");

    $sql = clubdata_mysqli::replaceDBprefix($sql, $this->_tablePrefix);

    if (function_exists('debug') )
      debug('CDDB',"[clubdata_mysqli: GetOne]: SQL after: $sql");

    return parent::GetOne($sql,$inputarr);
  }

  function &MetaColumnNames($table, $numIndexes=false,$useattnum=false /* only for postgres */)
  {
    if (function_exists('debug') )
      debug('CDDB',"[clubdata_mysqli: MetaColumnNames]: table before: $table");

    $table = clubdata_mysqli::replaceDBprefix($table, $this->_tablePrefix);

    if (function_exists('debug') )
      debug('CDDB',"[clubdata_mysqli: MetaColumnNames]: table after: $table");

    return parent::MetaColumnNames($table,$numIndexes);
  }

  function &SelectLimit($sql,$nrows=-1,$offset=-1, $inputarr=false,$arg3 = false, $secs2cache=0)
  {
    if (function_exists('debug') )
      debug('CDDB',"[clubdata_mysqli: selectlimit]: SQL before: $sql");

    $sql = clubdata_mysqli::replaceDBprefix($sql, $this->_tablePrefix);

    if (function_exists('debug') )
      debug('CDDB',"[clubdata_mysqli: selectlimit]: SQL after: $sql");

    return parent::selectlimit($sql,$nrows,$offset, $inputarr,$secs2cache);
  }


  /***************************************************************************/
  /************            STATIC FUNCTION                    ****************/
  /***************************************************************************/

  /**
   * This function replaces a string identifier <var>$prefix</var> with the
   * string held is the <var>_tablePrefix</var> class variable.
   * The code was adapted from joomla (libraries/joomla/database/database.php)
   *
   * @access public
   * @param string The SQL query
   * @param string The common table prefix
   */
  public static function replaceDBprefix($sql, $tablePrefix, $prefix='###_')
  {
    $sql = trim( $sql );

    $escaped = false;
    $quoteChar = '';

    $n = strlen( $sql );

    $startPos = 0;
    $literal = '';
    while ($startPos < $n) {
      $ip = strpos($sql, $prefix, $startPos);
      if ($ip === false) {
        break;
      }

      $j = strpos( $sql, "'", $startPos );
      $k = strpos( $sql, '"', $startPos );
      if (($k !== FALSE) && (($k < $j) || ($j === FALSE))) {
        $quoteChar      = '"';
        $j              = $k;
      } else {
        $quoteChar      = "'";
      }

      if ($j === false) {
        $j = $n;
      }

      $literal .= str_replace( $prefix, $tablePrefix, substr( $sql, $startPos, $j - $startPos ) );
      $startPos = $j;

      $j = $startPos + 1;

      if ($j >= $n) {
        break;
      }

      // quote comes first, find end of quote
      while (TRUE) {
        $k = strpos( $sql, $quoteChar, $j );
        $escaped = false;
        if ($k === false) {
          break;
        }
        $l = $k - 1;
        while ($l >= 0 && $sql{$l} == '\\') {
          $l--;
          $escaped = !$escaped;
        }
        if ($escaped) {
          $j      = $k+1;
          continue;
        }
        break;
      }
      if ($k === FALSE) {
        // error in the query - no end quote; ignore it
        break;
      }
      $literal .= substr( $sql, $startPos, $k - $startPos + 1 );
      $startPos = $k+1;
    }

  if ($startPos < $n) {
    $literal .= substr( $sql, $startPos, $n - $startPos );
  }

  return $literal;
}

}

/**
 * @package Clubdata
 */
class clubdata_rs_mysqli extends ADORecordSet_mysqli {

  function clubdata_rs_mysqli($queryID,$mode=false) {
    ADORecordSet_mysqli::ADORecordSet_mysqli($queryID,$mode);
  }
}

/**
 * @package Clubdata
 * 
 * Class if constant ADODB_EXTENSION is defined
 * ADODB_EXTENSION will be defined, if the ADODB extensions are installed.
 * 
 */
class clubdata_rs_ext_mysqli extends ADORecordSet_mysqli {

  function clubdata_rs_ext_mysqli($queryID,$mode=false) {
    ADORecordSet_mysqli::ADORecordSet_mysqli($queryID,$mode);
  }
}

$ADODB_NEWCONNECTION = 'clubdata_factory';

function& clubdata_factory($driver)
{
	if ($driver !== 'mysqli') return false;

	$driver = 'clubdata_'.$driver;
	$obj = new $driver();
	return $obj;
}

?>
