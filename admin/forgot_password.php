<?php
ini_set('display_errors', 'On');

require_once(__DIR__ . "/../config/admin/config.php");
require_once(__DIR__ . "/../db/src/Admin.php");
require_once(__DIR__ . "/../db/AdminManager.php");

// Set up doctrine objects
$emf = new EntityManagerFactory();
$em = $emf->getEntityManager();
$am = new AdminManager($em);

// Setup the template engine
require($_SERVER['DOCUMENT_ROOT'] . '/config/admin/mustache.php');

// If no action is specified, then we're showing the password reset form.
$tpl = $mustache->loadTemplate('forgot_password');

$data['page_title'] = 'Password Reset';
$data['title'] = 'Project Phoenix - Admin';

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
        $admin = $am->loadByEmail($_POST['email']);
      } catch (Exception $e) {
        $data['error'] = "Password could not be reset for supplied email address. Contact an admin for assistance.";
        break;
      }

      // If we didn't get a hit on the specified email address
      if($admin == NULL) {
        $data['error'] = "Password could not be reset for supplied email address. Contact an admin for assistance.";
        break;
      }

      // Generate a password reset token
      // HACK: loadByEmail returns an array, and unique emails are not enforced yet
      $admin = $admin[0];
      $admin->createResetHash();

      // Save it to the database
      $am->store($admin);

      // Send Reset Email
      // Based on example code here:
      // http://php.net/manual/en/function.mail.php

      // TODO: Move email content to a mustache template and build the message body
      // vs. having it embedded in the business logic.

      $resetLink = "https://"
        . $GLOBALS['HOST_NAME']
        . "/admin/forgot_password.php?action=doReset&token="
        . $admin->getResetHash()
        . "&email=" . $admin->getEmail() . "\r\n";

      $to = $admin->getEmail();
      $subject = "Password Reset for Project Phoenix";
      $message = "An attempt has been made to reset your password." . "\r\n"
                . "To reset your password, click this link:" . $resetLink . "\r\n"
                . $admin->getResetHash()
                . "&email=" . $admin->getEmail() . "\r\n"
                . "This reset link will expire in "
                . ($GLOBALS['PASSWORD_RESET_TIMEOUT']/60) . " minutes.\r\n";

      $headers  = "From: noreply@jeromie.com" . "\r\n"
                . "Reply-To: noreply@jeromie.com" . "\r\n";

      mail($to, $subject, $message, $headers);

      $data['resetLink'] = $resetLink;

    break;
    case "doReset":

      $tpl = $mustache->loadTemplate('password_update');
      try {
        $admin = $am->loadByEmail($_GET['email']);
      } catch (Exception $e) {
        $data['error'] = "Password could not be reset for supplied email address. Contact an admin for assistance.";
        break;
      }

      // If we didn't get a hit on the specified email address
      if($admin == NULL) {
        $data['error'] = "Password could not be reset for supplied email address. Contact an admin for assistance.";
      }

      // HACK: loadByEmail returns an array, and unique emails are not enforced yet
      $admin = $admin[0];

      // Validate the reset hash
      if(!$admin->validateResetHash($_GET['token'])) {
        $data['error'] = "Password could not be reset for supplied email address.  Password reset token is invalid or expired.";
        break;
      } else {
        $data['email'] = $admin->getEmail();
        $data['token'] = $_GET['token'];
      }
    break;
    case "updatePassword":

    $tpl = $mustache->loadTemplate('password_update_complete');
    try {
      $admin = $am->loadByEmail($_POST['email']);
    } catch (Exception $e) {
      $data['error'] = "Password could not be reset for supplied email address. Contact an admin for assistance.";
      break;
    }

    // If we didn't get a hit on the specified email address
    if($admin == NULL) {
      $data['error'] = "Password could not be reset for supplied email address. Contact an admin for assistance.";
    }

    // HACK: loadByEmail returns an array, and unique emails are not enforced yet
    $admin = $admin[0];

    // Validate the reset hash
    if(!$admin->validateResetHash($_POST['token'])) {
      $data['error'] = "Password could not be reset for supplied email address.  Password reset token is invalid or expired.";
      break;
    } else {
      $admin->setPassword($_POST['password']);
      $am->store($admin);
      $data['updated'] = true;
      $data['destination'] = "/admin/index.php";
    }
  }
}

// Pass the resulting data into the template
echo $tpl->render($data);
?>
