
function removeSpinner(){
  //remove the spinner after animation completes
  var spinner = document.getElementById("spinner-wrapper");
  if (spinner) {
    spinner.remove(0);
  }

  //change pdf position relative to ensure page layout does not chagne
  var pdf = document.getElementById(certificate);
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
