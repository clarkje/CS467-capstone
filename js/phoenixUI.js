
function removeSpinner(){
  //remove the spinner after animation completes
  var spinner = document.getElementById("spinner-wrapper");
  spinner.remove(0);

  //change pdf position relative to ensure page layout does not chagne
  var pdf = document.getElementById(certificate);
  certificate.style.position = "relative";
}

function showPDF(){
  //change pdf display relative to ensure page layout does not chagne
  var pdf = document.getElementById("certificate");
  pdf.style.display = "inline-block";
}

myVar = setTimeout(removeSpinner, 6500);

pdf = setTimeout(showPDF, 6000);

//Change the preview image on create award
document.addEventListener('DOMContentLoaded', function(){
	var select = document.getElementById("template");

	select.addEventListener('change', function(){
  		var template = select.options[select.selectedIndex].text;

  		var image = document.getElementById("cert-preview");

  		if (template == "Outstanding"){
    			image.src="/cert_assets/outstanding.png";
  		}
  		else if (template == "Winner"){
    			image.src="/cert_assets/winner.png";
  		}
  		else {
    			image.src="/cert_assets/congratulations.png";
  		}
	});
});
