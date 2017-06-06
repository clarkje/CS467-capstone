
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
  document.getElementById('certificate').src = document.getElementById('iframeid').src
}

myVar = setTimeout(removeSpinner, 6500);

pdf = setTimeout(showPDF, 6000);
