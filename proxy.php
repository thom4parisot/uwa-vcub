<?php
define('CACHE_TTL', is_int($_GET['cache']) ? $_GET['cache'] : 3600);
define('CACHE_FOLDER', dirname(__FILE__).'/cache');
define('USE_CACHE', true);
//
$session = curl_init($_GET['url']);

// If it's a POST, put the POST data in the body
if (isset($_POST) && !empty($_POST))
{
  $postvars = '';
  while ($element = current($_POST))
  {
    $postvars .= key($_POST).'='.$element.'&';
    next($_POST);
  }
  curl_setopt ($session, CURLOPT_POST, true);
  curl_setopt ($session, CURLOPT_POSTFIELDS, $postvars);
}

// Play with some cache
$md5sign = md5($_GET['url'].$postvars);
$md5file = CACHE_FOLDER.'/'.$md5sign;

/*
 * Read cache
 */
if (defined('USE_CACHE') && USE_CACHE === true && file_exists($md5file) && filemtime($md5file)+CACHE_TTL > time())
{
  curl_close($session);
  send_headers_content_type($_GET['type']);
  readfile($md5file);
  exit();
}

// Don't return HTTP headers. Do return the contents of the call
curl_setopt($session, CURLOPT_HEADER, false);
curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

// Make the call
$output = curl_exec($session);

if (defined('USE_CACHE') && USE_CACHE === true)
{
  $fp = fopen($md5file, "wb+");
  fwrite($fp, $output);
  fclose($fp);
}

send_headers_content_type($_GET['type']);
echo $output;
curl_close($session);

/*
 * Functions
 */
function send_headers_content_type($type)
{
  // Set the Content-Type appropriately
  switch ($type)
  {
    case 'text':
    default:
      header("Content-Type: text/plain");
    break;

    case 'xml':
      header("Content-Type: text/xml");
    break;

    case 'json':
      header('Content-Type: text/x-json');
    break;
  }
}
