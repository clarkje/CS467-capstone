
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

myVar = setTimeout(removeSpinner, 4000);

pdf = setTimeout(showPDF, 2000);