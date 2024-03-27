function init() {
    $(".searchInput").autocomplete (
        siteUrl+"/index.php?ajaxRequest=searchSuggestion",
        {
            extraParams: { title: function(){ return $(".searchInput").val(); } },
            cacheLength: 0,
            autoFill: true
        }
    );
    
    $(".searchInput").click(function() { if(this.value==searchTerm){this.value='';} });
    $(".loginLink").click( function () {
       ; 
    });
}

//centering popup
function centerPopup(){
    //request data for centering
    var windowWidth = document.documentElement.clientWidth;
    var windowHeight = document.documentElement.clientHeight;
    var popupHeight = $("#popupContact").height();
    var popupWidth = $("#popupContact").width();
    //centering
    $("#popupContact").css({
        "position": "absolute",
        "top": windowHeight/2-popupHeight/2,
        "left": windowWidth/2-popupWidth/2
    });
    //only need force for IE6
    
    $("#backgroundPopup").css({
        "height": windowHeight
    });

}