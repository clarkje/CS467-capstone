<?php

require_once(__DIR__ . "/../config/client/config.php");
require_once(__DIR__ . "/../db/src/Award.php");

/**
* Generates certificates in the background
*/

class CertGenerator {

  public function createCertificate(Award $award) {

    // Generate TeX File and save it to the filesystem
    // This operation is fast, so we just do it in realtime.

    $output = $this->generateTexOutput($award);

    if(empty($output)) {
      // TODO: Throw an exception
        echo("CertGenerator::createCertificate - Generating TeX File Failed");
        return false;
    }

    $basePath = $GLOBALS['STATIC_ROOT'] . $GLOBALS['CERT_PATH'];
    $filePath = $basePath . $award->getCertPath() . ".tex";

    // Create the file if it does not exist, otherwise truncate and overwrite
    $fh = fopen($filePath, "w+");
    if(!fwrite($fh, $output)) {
      echo("CertGenerator::createCertificate - Writing TeX File Failed");
      return false;
    }

    $basePath = $GLOBALS['STATIC_ROOT'] . $GLOBALS['CERT_PATH'];
    $texFilePath = $basePath . $award->getCertPath() . ".tex";

    // Unfortunate workaround for the lack of an outputFile parameter...
    $outputDirectory = $basePath;
    $outputDirectory .= substr($award->getCertPath(), 0, 3);

    // Backgrounding Latex generation at shell borrowed from: https://segment.com/blog/how-to-make-async-requests-in-php/
    $command = $GLOBALS['PDFLATEX_PATH'] . " --interaction=nonstopmode --output-directory=" . $outputDirectory . " " . $texFilePath . " > /dev/null 2>&1 &";
    system($command);

    $this->emailAward($award);
  }

  // Mustaches and TeX both use curly braces, so we're doing this the ugly way.
  private function generateTexOutput(Award $award) {

    //determine template type for award text
    $type = $award->getAwardType();
    $description = $type->getDescription();

    if ($description == "Outstanding"){
      $awardText = "You Are Awesome";
    }
    else if ($description == "Winner"){
      $awardText = "Employee Of The Month";
    }
    else {
      $awardText = "Top Corporate Achiever";
    }

    $text = "\documentclass[12pt]{article}

      	% Packages
        % ---
      \usepackage{graphicx} % Add pictures to your document
      \usepackage{wallpaper}
      \usepackage[letterpaper, margin=2cm, nohead]{geometry}
      \usepackage[export]{adjustbox}
      \usepackage{tgbonum}

      \begin{document}
      	\linespread{2}\selectfont
      	\pagestyle{empty}
      	\TileWallPaper{1\paperwidth}{1\paperheight}{{" . $GLOBALS['DOCUMENT_ROOT'] . "/cert_assets/" . $award->getAwardType()->getTemplateFile() . "}}
      	\begin{center}
      		\\vspace*{80mm}
      	 		{\\fontfamily{qbk}\selectfont\Huge {" . $awardText . "}}
      	 		\\begin{center}
      	 			{awarded to}
      	 		\\end{center}
      			\\begin{center}
      				{\Huge {" . $award->getRecipientFirst() . " "  . $award->getRecipientLast() . "}}
      			\\end{center}
      			\\begin{center}
      				{presented by}
      			\\end{center}
      			\\begin{center}
      				{{" . $award->getGranter()->getFirstName() . " " . $award->getGranter()->getLastName() . "}}
      			\\end{center}
      			\\begin{center}
      				{{" . $award->getGrantDate()->format('m/d/Y') . "}}
      			\\end{center}
      			\\begin{figure}[h]
      				\\begin{subfigure}
      					\\includegraphics[width=0.5\linewidth, height=5cm]{" . $GLOBALS['DOCUMENT_ROOT'] . "/cert_assets/globologo.png}
      				\\end{subfigure}
      				\\begin{subfigure}
      					\\includegraphics[width=0.5\linewidth, height=5cm]{" . $GLOBALS['STATIC_ROOT'] . $GLOBALS['SIG_PATH'] . $award->getGranter()->getSignaturePath() . "}
      				\\end{subfigure}
      			\\end{figure}
      		\\vfill
      	\\end{center}
      \\end{document}";
      return $text;
  }

  private function emailAward(Award $award) {

    $mail = new PHPMailer;
    $mail->isSendmail();
    $mail->setFrom('noreply@phoenix.jeromie.com', 'Award Notification');
    $mail->addReplyTo('noreply@phoenix.jeromie.com', 'Award Notification');

    $recipientName = $award->getRecipientFirst() . " " . $award->getRecipientLast();

    $email = $award->getRecipientEmail();
    $mail->addAddress($email, $recipientName);
    // $mail->addAttachment($award->getCertPath() . ".pdf");
    $mail->isHTML(true);

    $mail->Subject = "An award has been issued to you";
    $body = "Dear " . $award->getRecipientFirst() . " " . $award->getRecipientLast() . "<br>";
    $body .= "<p>You have received an award from " . $award->getGranter()->getFirstName() . " " . $award->getGranter()->getLastName() . "</p>";
    $body .= "<p>We here at GloboCorp value your <i>Important Contribution</i>.  Please print the attached PDF and display it proudly as evidence of your worth as a human.</p>";
    $body .= "<a href='" . $award->getCertURL() . "'>Click Here</a> to retrieve your award.<br><br>";

    $body .= "Sincerely:<br> Your Employee Output Maximization Department";

    $mail->msgHTML = $body;
    $mail->Body = $body;
    if(!$mail->send()) {
      echo($mail->ErrorInfo);
      echo 'Message could not be sent';
    }
  }

}
?>
