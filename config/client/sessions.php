<?php

require_once($GLOBALS['DOCUMENT_ROOT'] . "/db/UserManager.php");

// TODO: Translate those recommendations and INI settings to ini_set directives and confirm they work
ini_set("session.name", "CID");

/**
* Manage user sessions
*
* OWASP Recommendations for secure session management from:
* https://www.owasp.org/index.php/Session_Management_Cheat_Sheet
*
* - Use a generic name for the session ID, (like 'id') to avoid fingerprinting
* -- Set session.name = ID in php.ini
* - Session token should be at least 128 bits
* -- Set session.entropy_length = 32 in php.ini
* - Must use a secure PNRG for entropy
* -- session.entropy_file = /dev/urandom
* - Prefer Cookies or POST to avoid token disclosure in logs, etc.
* -- session.use_cookies = 1
* - TODO: Don't swtich between mechanisms (Cookies to GET to POST, etc).  Invalidate the session.
* - Session *must* happen over SSL
* -- setup server-side redirect to HTTPS for all requests
* TODO: Validate that PHP is already doing these
* - The "secure" cookie attribute must be set to ensure that the session token is sent through an encrypted channel
* - The "HttpOnly" cookie attribute should be set to prevent scripts from accessing the session cookie data
* - The "sameSite" cookie attribute should be set to prevent leaking the session cookie contents to other domains
* TODO: Think about these
* - "Domain" and "Path" attributes further limit where cookie data can be sent
* - Expire and Max-Age attributes should be set to conservative values
* -- session.cache_expire = 30
* -- session.gc_maxlifetime = 1440
* - Sessions should expire after 5-30 minutes
* - Use restrictive cache directives;
* - + Cache-Control: no-cache="Set-Cookie, Set-Cookie2" to prevent caching the session ID
*/


// If the user logs out, destroy the session
if (isset($_REQUEST['action']) && $_REQUEST['action'] == "logout") {
  destroySession();
} else {
  // Start the session handling mechanism
  session_start();

  // If the user doesn't have an active session, create one.
  if (!isset($_SESSION['created'])) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
  }

  // Defend against session fixation attacks by regenerating the session ID periodically
  if ($_SESSION['created'] < time() - 300) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
  }
}

// If the login fails, pass the message out to the template.
// TODO: Might be more elegant to do this with exceptions
$data['error'] = handleLogin();

// borrowed from the example here:
// http://php.net/manual/en/function.session-destroy.php
function destroySession() {
  // Initialize the session.
  // If you are using session_name("something"), don't forget it now!
  session_start();

  // Unset all of the session variables.
  $_SESSION = array();

  // If it's desired to kill the session, also delete the session cookie.
  // Note: This will destroy the session, and not just the session data!
  if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000,
          $params["path"], $params["domain"],
          $params["secure"], $params["httponly"]
      );
  }

  // Finally, destroy the session.
  session_destroy();
}


/**
* @return string error string
*/

function handleLogin() {
  // Handle a login Event
  if (isset($_POST['action']) && $_POST['action'] == "login") {

    // Check the credentials
    $emf = new EntityManagerFactory();
    $em = $emf->getEntityManager();
    $um = new UserManager($em);

    // HACK: $am->loadByEmail returns an array.
    // TODO: Not sure that I care, but email should probably be unique for admins.
    $users = $um->loadByEmail($_POST['email']);

    if (sizeof($users) > 0) {
      $user = $users[0];
      if ($user->verifyPassword($_POST['password'])) {
        $_SESSION['logged_in'] = true;
      } else {
        return "Authentication Error: The supplied username and password did not match.";
      }
    } else {
      return "Authentication Error: The supplied username and password did not match.";
    }
    // Store our user object in the session
    $_SESSION['id'] = $user->getId();
    $_SESSION['email'] = $user->getEmail();
  }
  return null;
}
?>
