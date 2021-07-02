// Load with attribute defer!
// When the user scrolls down 20px from the top of the document, show the button
window.onscroll = function() {scrollFunction()};

function scrollFunction()
{
	if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20)
	{
    document.getElementById("toTop").style.display = "block";
  }
	else
	{
    document.getElementById("toTop").style.display = "none";
  }
}
/* Ich mache jetzt doch ohne, mit normalem #TOP-Link, weil sonst
TOC-Sprungmarken nicht zur�ckgesetzt werden.
function topFunction()
{
	document.body.scrollTop = 0; // For Safari
	document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
}*/