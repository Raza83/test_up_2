function show_del_filter()
{
  var x = document.getElementById("bulk_del_form");
  
  if (x.style.display === "none") 
  {
    x.style.display = "block";
  }
  else if(x.style.display === "block")
  {
    x.style.display = "none";
  }

//alert('abc');
}

jQuery(document).ready(function() {
    jQuery('#example').DataTable();

    jQuery('#example1').DataTable();

    console.log("abc");

} );