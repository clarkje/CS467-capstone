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
    if(!$award->hasTex()) {

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
    }

    if(!$award->hasCert()) {

      $basePath = $GLOBALS['STATIC_ROOT'] . $GLOBALS['CERT_PATH'];
      $texFilePath = $basePath . $award->getCertPath() . ".tex";

      // Unfortunate workaround for the lack of an outputFile parameter...
      $outputDirectory = $basePath;
      $outputDirectory .= substr($award->getCertPath(), 0, 3);

      // Backgrounding Latex generation at shell borrowed from: https://segment.com/blog/how-to-make-async-requests-in-php/
      $command = $GLOBALS['PDFLATEX_PATH'] . " --interaction=nonstopmode --output-directory=" . $outputDirectory . " " . $texFilePath . " > /dev/null 2>&1 &";
      system($command);
    }
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
}
?>
