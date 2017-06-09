<?php
ini_set('display_errors', 'On');

require_once(__DIR__ . "/config/client/config.php");
require_once(__DIR__ . "/db/src/User.php");
require_once(__DIR__ . "/db/UserManager.php");

// Set up doctrine objects
$emf = new EntityManagerFactory();
$em = $emf->getEntityManager();
$userManager = new UserManager($em);

// Setup the template engine
require($_SERVER['DOCUMENT_ROOT'] . '/config/client/mustache.php');

// If no action is specified, then we're showing the password reset form.
$tpl = $mustache->loadTemplate('forgot_password');

$data['page_title'] = 'Password Reset';
$data['title'] = 'Project Phoenix - Employee Recognition System';

// Otherwise, we're handling some kind of action in the reset workflow
if(isset($_REQUEST['action'])) {
  switch($_REQUEST['action']) {

    // Create a password reset hash and send an email to the affected user
    case "reset":

      // We expect this response to come as a POST.  If it doesn't, it's sketchy.
      if (!(isset($_POST['action']) && $_POST['action'] == "reset")) {
        $data['error'] = "Password could not be reset for supplied email address. Contact an admin for assistance.";
      }

      $tpl = $mustache->loadTemplate('password_reset');

      try {
        $user = $userManager->loadByEmail($_POST['email']);
      } catch (Exception $e) {
        $data['error'] = "Password could not be reset for supplied email address. Contact an admin for assistance.";
        break;
      }

      // If we didn't get a hit on the specified email address
      if($user == NULL) {
        $data['error'] = "Password could not be reset for supplied email address. Contact an admin for assistance.";
        break;
      }

      // Generate a password reset token
      // HACK: loadByEmail returns an array, and unique emails are not enforced yet
      $user = $user[0];
      $user->createResetHash();

      // Save it to the database
      $userManager->store($user);

      // Send Reset Email
      // Based on example code here:
      // http://php.net/manual/en/function.mail.php

      // TODO: Move email content to a mustache template and build the message body
      // vs. having it embedded in the business logic.

      $resetLink = "https://"
        . $GLOBALS['HOST_NAME']
        . "/forgot_password.php?action=doReset&token="
        . $user->getResetHash()
        . "&email=" . $user->getEmail() . "\r\n";

      $to = $user->getEmail();
      $subject = "Password Reset for Project Phoenix";
      $message = "An attempt has been made to reset your password." . "\r\n"
                . "To reset your password, click this link:" . $resetLink . "\r\n"
                . $user->getResetHash()
                . "&email=" . $user->getEmail() . "\r\n"
                . "This reset link will expire in "
                . ($GLOBALS['PASSWORD_RESET_TIMEOUT']/60) . " minutes.\r\n";

      $headers  = "From: noreply@phoenix.jeromie.com" . "\r\n"
                . "Reply-To: noreply@phoenix.jeromie.com" . "\r\n";

      mail($to, $subject, $message, $headers);

      $data['resetLink'] = $resetLink;

    break;
    case "doReset":

      $tpl = $mustache->loadTemplate('password_update');
      try {
        $user = $userManager->loadByEmail($_GET['email']);
      } catch (Exception $e) {
        $data['error'] = "Password could not be reset for supplied email address. Contact an admin for assistance.";
        break;
      }

      // If we didn't get a hit on the specified email address
      if($user == NULL) {
        $data['error'] = "Password could not be reset for supplied email address. Contact an admin for assistance.";
      }

      // HACK: loadByEmail returns an array, and unique emails are not enforced yet
      $user = $user[0];

      // Validate the reset hash
      if(!$user->validateResetHash($_GET['token'])) {
        $data['error'] = "Password could not be reset for supplied email address.  Password reset token is invalid or expired.";
        break;
      } else {
        $data['email'] = $user->getEmail();
        $data['token'] = $_GET['token'];
      }
    break;
    case "updatePassword":

    $tpl = $mustache->loadTemplate('password_update_complete');
    try {
      $user = $userManager->loadByEmail($_POST['email']);
    } catch (Exception $e) {
      $data['error'] = "Password could not be reset for supplied email address. Contact an admin for assistance.";
      break;
    }

    // If we didn't get a hit on the specified email address
    if($user == NULL) {
      $data['error'] = "Password could not be reset for supplied email address. Contact an admin for assistance.";
    }

    // HACK: loadByEmail returns an array, and unique emails are not enforced yet
    $user = $user[0];

    // Validate the reset hash
    if(!$user->validateResetHash($_POST['token'])) {
      $data['error'] = "Password could not be reset for supplied email address.  Password reset token is invalid or expired.";
      break;
    } else {
      $user->setPassword($_POST['password']);
      $userManager->store($user);
      $data['updated'] = true;
      $data['destination'] = "/index.php";
    }
  }
}

// Pass the resulting data into the template
echo $tpl->render($data);
?>
