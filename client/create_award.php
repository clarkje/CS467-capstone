<?php

require_once(__DIR__ . "/../config/client/config.php");

// Setup the template engine
require($_SERVER['DOCUMENT_ROOT'] . '/config/client/mustache.php');
$tpl = $mustache->loadTemplate('create_award');

$data['title'] = 'Project Phoenix - Client';
$data['page_title'] = 'Create Award';

function writeTexFile($awardType, $receiver, $sender, $template, $filename){
		//We might want to create this file in a seperate directory instead of dumping into the current one?
	 	$myfile = fopen($filename, "w") or die("Unable to open file!");
		$txt = "\documentclass[12pt]{article}

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
	\TileWallPaper{1\paperwidth}{1\paperheight}{{$template}}

	\begin{center}
		\\vspace*{80mm}
	 		{\\fontfamily{qbk}\selectfont\Huge {$awardType}}
	 		\\begin{center}
	 			{awarded to}
	 		\\end{center}
			\\begin{center}
				{\Huge {$receiver}}
			\\end{center}
			\\begin{center}
				{presented by}
			\\end{center}
			\\begin{center}
				{{$sender}}
			\\end{center}
			\\begin{center}
				{Oct 2017}
			\\end{center}
			\\begin{figure}[h]
				\\begin{subfigure}
					\\includegraphics[width=0.5\linewidth, height=5cm]{globologo.png} 
				\\end{subfigure}
				\\begin{subfigure}
					\\includegraphics[width=0.5\linewidth, height=5cm]{mShivers.png}
				\\end{subfigure}
			\\end{figure}
		\\vfill
	\\end{center}
\\end{document}";
		fwrite($myfile, $txt);
		fclose($myfile);
}

if(isset($_POST['action'])){
			//Get value from <select> tag
			$selected = $POST['awardTemplates'];
			switch($_POST['awardTemplates']){
				case 'outstanding':
					$data['image'] = 'outstanding.png';
					break;
				case 'winner':
					$data['image'] = 'winner.png';
					break;
				case 'congratulations':
					$data['image'] = 'congratulations.png';
					break;
			}
	
	$awardType = $_POST['award-type'];
	$receiverF_NAME = $_POST['firstName'];
	$receiverL_NAME = $_POST['lastName'];
	$receiver = $receiverF_NAME . " " . $receiverL_NAME;
	$email = $_POST['email'];
	$sender = $_POST['currentUser'];
	$template = $_POST['awardTemplates'] . ".png";
	$filename = $_POST['awardTemplates'] . ".tex";
	$pdf_filename = $_POST['awardTemplates'] . ".pdf";
	//echo ($receiver . $email . $sender . $template . "<br />\n");
	writeTexFile($awardType, $receiver, $sender, $template, $filename);

	//loop until file has been created and is found in the directory
	$exists = FALSE;
	while($exists = FALSE){
		$exists = file_exists($filename);
	}
	
	//build pdf
	system("pdflatex --interaction=nonstopmode " . $filename, $returnVar);
	//echo("Return: " . $returnVar . "<br />\n");

	//loop on checking for pdf until found
	$exists = FALSE;
	$count = 0;
	while($exists = FALSE){
		$exists = file_exists($pdf_filename);
		$count++;

		//Not sure how long to loop. The pdflatex command takes several seconds to generate a pdf. Loop should continue for some time, then break if pdf is never generated. 
		if($count == 200000){
			$data['alert'] = "Something is wrong. Award not created.";
			break;
		}
	}
	$data['alert'] = "Award successfully created";


}

echo $tpl->render($data);

?>