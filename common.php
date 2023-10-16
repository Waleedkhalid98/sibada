<?php

/*********************************************************************************
 *       Filename: common.php
 *       PHP 4.0 & Templates build 11/30/2001
 *********************************************************************************/

error_reporting (E_ALL ^ E_NOTICE);
@include("./template.php");
//===============================
// Database Connection Definition
//-------------------------------
//medhelp Connection begin

//include("./db_mysql.inc");
include("../librerie/db_mysql.inc");
//include("../librerie/db_oracle.inc");
require_once("../imysql_sibada.php");

define("DATABASE_NAME",DBNAME_SS);
define("DOMANDA_ECONOMICA","1");
define("DOMANDA_DOMICILIARE","2");
define("DOMANDA_RICOVERO","3");
define("DOMANDA_MATERNITA","4");
define("DOMANDA_NUCLEO","5");
define("DOMANDA_EDUCATIVA","6");
define("DOMANDA_SEGRETARIATO","7");
define("DOMANDA_TRIBUNALE","8");
define("DOMANDA_PROGETTO","9");
define("DOMANDA_ASILO","10");
define("DOMANDA_AFFITTO","11");
define("DOMANDA_LIBRO","12");
define("DOMANDA_STUDIO","13");
define("DOMANDA_MENSA","14");
define("DOMANDA_TRASPORTI","15");
define("DOMANDA_SCOLASTICA","16");

/*
include_once("../librerie/class.oracle.php");
$o = new Oracle(array('username'=>OCI_DBUSER,'password'=>OCI_DBPASSWORD,'database'=>OCI_DBHOST."/".OCI_DBNAME));
$o->connect();
*/

// Database Initialize
$db = new DB_Sql();
$db->Database = DATABASE_NAME;
$db->User     = DATABASE_USER;
$db->Password = DATABASE_PASSWORD;
$db->Host     = DATABASE_HOST;

// Database Initialize
$db_front = new DB_Sql();
$db_front->Database = FRONT_ESONAME;
$db_front->User     = FRONT_ESOUSER;
$db_front->Password = FRONT_ESOPWD;
$db_front->Host     = FRONT_ESOHOST;

//===============================
// Site Initialization
//-------------------------------
// Obtain the path where this site is located on the server
//-------------------------------
$app_path = ".";
//===============================

//===============================
// Common functions
//-------------------------------
// Convert non-standard characters to HTML
//-------------------------------
function tohtml($strValue)
{
  return htmlspecialchars($strValue);
}

//-------------------------------
// Convert value to URL
//-------------------------------
function tourl($strValue)
{
  return urlencode($strValue);
}

//-------------------------------
// Obtain specific URL Parameter from URL string
//-------------------------------
function get_param($param_name)
{

  $sPAGE=str_replace(".","_",basename($_SERVER['PHP_SELF']));
  $param_value = "";
  if(isset($_POST[$param_name]))
    $param_value = $_POST[$param_name];
  else if(isset($_GET[$param_name]))
    $param_value = $_GET[$param_name];


  if (!$param_value && $_SESSION[$sPAGE] && !$_REQUEST["_ricerca"])
  {
    $aCOOKIE=unserialize($_SESSION[$sPAGE]);
    $param_value=$aCOOKIE[$param_name];
  }

  return $param_value;
}

function get_session($parameter_name)
{
  global $HTTP_SESSION_VARS;
  return isset($HTTP_SESSION_VARS[$parameter_name]) ? $HTTP_SESSION_VARS[$parameter_name] : "";
}

function set_session($parameter_name, $parameter_value)
{
  global $HTTP_SESSION_VARS;
 	global ${$parameter_name};
  if(session_is_registered($parameter_name)) {
    session_unregister($parameter_name);
	}

  ${$parameter_name} = $parameter_value;
  session_register($parameter_name);
  $HTTP_SESSION_VARS[$parameter_name] = $parameter_value;
}

function is_number($string_value)
{
  if(is_numeric($string_value) || !strlen($string_value))
    return true;
  else
    return false;
}

//-------------------------------
// Convert value for use with SQL statament
//-------------------------------

/*function tosql($value, $type)
{
  if(!strlen($value))
    return "NULL";
  else
    if($type == "Number")
      return str_replace (",", ".", doubleval($value));
    else
    {
      if(get_magic_quotes_gpc() == 0)
      {
        $value = str_replace("'","''",$value);
        $value = str_replace("\\","\\\\",$value);
      }
      else
      {
        $value = str_replace("\\'","''",$value);
        $value = str_replace("\\\"","\"",$value);
      }

      return "'" . $value . "'";
    }
}
*/


function tosql($value, $type="Text")
{
  if(!strlen($value))
    return "NULL";
  else
    if($type == "Number")
    {
      return str_replace (",", ".", doubleval($value));
    }
    elseif($type == "Euro")
    {
    	return numberToSql($value);
    }
    else
    {
      if(get_magic_quotes_gpc() == 0)
      {
        $value = str_replace("'","\'",$value);
        $value = str_replace("\\","\\\\",$value);
      }
      else
      {
        $value = str_replace("\\'","\'",$value);
        $value = str_replace("\\\"","\"",$value);
      }

      return "\"" . $value . "\"";
    }
}



function strip($value)
{
  if(get_magic_quotes_gpc() == 0)
    return $value;
  else
    return stripslashes($value);
}

function db_fill_array($sql_query)
{
  global $db;
  $db_fill = new DB_Sql();
  $db_fill->Database = $db->Database;
  $db_fill->User     = $db->User;
  $db_fill->Password = $db->Password;
  $db_fill->Host     = $db->Host;

  $db_fill->query($sql_query);
  if ($db_fill->next_record())
  {
    do
    {
      $ar_lookup[$db_fill->f(0)] = $db_fill->f(1);
    } while ($db_fill->next_record());
    return $ar_lookup;
  }
  else
    return false;


}

//-------------------------------
// Deprecated function - use get_db_value($sql)
//-------------------------------
function dlookup($table_name, $field_name, $where_condition)
{
  $sql = "SELECT " . $field_name . " FROM " . $table_name . " WHERE " . $where_condition;
  return get_db_value($sql);
}


//-------------------------------
// Lookup field in the database based on SQL query
//-------------------------------
function get_db_value($sql)
{
  global $db;
  $db_look = new DB_Sql();
  $db_look->Database = $db->Database;
  $db_look->User     = $db->User;
  $db_look->Password = $db->Password;
  $db_look->Host     = $db->Host;

  $db_look->query($sql);
  if($db_look->next_record())
  {
    $result=$db_look->f(0);
    $db_look->closeCONNECTION();
    return $result;
  }
  else
  {
    $db_look->closeCONNECTION();
    return "";
  }
}

//-------------------------------
// Obtain Checkbox value depending on field type
//-------------------------------
function get_checkbox_value($value, $checked_value, $unchecked_value, $type)
{
  if(!strlen($value))
    return tosql($unchecked_value, $type);
  else
    return tosql($checked_value, $type);
}

//-------------------------------
// Obtain lookup value from array containing List Of Values
//-------------------------------
function get_lov_value($value, $array)
{
  $return_result = "";

  if(sizeof($array) % 2 != 0)
    $array_length = sizeof($array) - 1;
  else
    $array_length = sizeof($array);
  reset($array);

  for($i = 0; $i < $array_length; $i = $i + 2)
  {
    if($value == $array[$i]) $return_result = $array[$i+1];
  }

  return $return_result;
}

//-------------------------------
// Verify user's security level and redirect to login page if needed
//-------------------------------

function check_security()
{
  $return_page = getenv("REQUEST_URI");
  if($return_page === "") { $return_page = getenv("SCRIPT_NAME") . "?" . getenv("QUERY_STRING"); }
  if(!session_is_registered("UserID"))
  {
    header ("Location: .php?querystring=" . urlencode(getenv("QUERY_STRING")) . "&ret_page=" . urlencode($return_page));
    exit;
  }
}

function front_db_fill_array($sql_query)
{
  global $db_front;
  $db_fill = new DB_Sql();
  $db_fill->Database = $db_front->Database;
  $db_fill->User     = $db_front->User;
  $db_fill->Password = $db_front->Password;
  $db_fill->Host     = $db_front->Host;

  $db_fill->query($sql_query);
  if ($db_fill->next_record())
  {
    do
    {
      $ar_lookup[$db_fill->f(0)] = $db_fill->f(1);
    } while ($db_fill->next_record());
    return $ar_lookup;
  }
  else
    return false;

}

function front_get_db_value($sql)
{
  global $db_front;
  $db_look = new DB_Sql();
  $db_look->Database = $db_front->Database;
  $db_look->User     = $db_front->User;
  $db_look->Password = $db_front->Password;
  $db_look->Host     = $db_front->Host;

  $db_look->query($sql);
  if($db_look->next_record())
    return $db_look->f(0);
  else 
    return "";
}


//===============================
//  GlobalFuncs begin
include("../libreria.php");
include("../lib.servizi.sociali.php");
//  GlobalFuncs end
//===============================
?>
