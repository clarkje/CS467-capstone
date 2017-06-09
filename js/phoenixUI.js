
function removeSpinner(){
  //remove the spinner after animation completes
  var spinner = document.getElementById("spinner-wrapper");
  if (spinner) {
    spinner.remove(0);
  }

  //change pdf position relative to ensure page layout does not chagne
  var pdf = document.getElementById("certificate");
  if (pdf) {
    certificate.style.position = "relative";
  }
}

function showPDF(){
  //change pdf display relative to ensure page layout does not chagne
  var pdf = document.getElementById("certificate");
  if(pdf) {
    pdf.style.display = "inline-block";
  }
}

// These calls should really be moved to the page(s) they apply to
myVar = setTimeout(removeSpinner, 6500);
pdf = setTimeout(showPDF, 6000);

//Change the preview image on create award
document.addEventListener('DOMContentLoaded', function(){
	var select = document.getElementById("template");
	var image = document.getElementById("cert-preview");
	image.src="/cert_assets/outstanding_sample.png";
	
	select.addEventListener('change', function(){
  		var template = select.options[select.selectedIndex].text;

  		//var image = document.getElementById("cert-preview");

  		if (template == "Outstanding"){
    			image.src="/cert_assets/outstanding_sample.png";
  		}
  		else if (template == "Winner"){
    			image.src="/cert_assets/winner_sample.png";
  		}
  		else {
    			image.src="/cert_assets/congratulations_sample.png";
  		}
	});
});
