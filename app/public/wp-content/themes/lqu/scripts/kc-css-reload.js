jQuery(document).ready( function() {
  //Reload style sheets on update
  function autoReloadCSS(sheetid,interval) {
    console.log('Ready to reload CSS');
    var sheet = document.getElementById(sheetid)
    var src = sheet.getAttribute('href');
    setInterval( function() {
        //console.log('Reloading '+sheetid);
        newsrc = src+'?ar='+(new Date).getTime();
        sheet.setAttribute('href',newsrc)
    },interval*1000)
  };
  autoReloadCSS('lqu-style-css',1); //5 is 5 seconds.
});
