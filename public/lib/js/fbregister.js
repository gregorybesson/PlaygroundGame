$(function(){
    
    // the 2 following js var are generated from PlaygroundUser\View\Helper\FacebookLogin
    var APP_ID =  FbDomainAuthId;
    var APP_SCOPE = FbDomainAuthScope;
    
    /**** Facebook init */
    window.fbAsyncInit = function() {
      // init the FB JS SDK
      FB.init({
        appId      : APP_ID, // App ID from the App Dashboard
        status     : true, // check the login status upon init?
        cookie     : true, // set sessions cookies to allow your server to access the session?
        xfbml      : true  // parse XFBML tags on this page?
      });
      FB.Canvas.setAutoGrow();
      FB.Canvas.getPageInfo(function (pageInfo)
      {
         $({y: pageInfo.scrollTop}).animate(
            {y: 0},
            {
                duration: 0,
                step: function (offset)
                {
                    FB.Canvas.scrollTo(0, offset);
                }
            }
         );
      });
    };
    
    $('#fb-play').click(function(event){
        event.preventDefault();
        _this = $(this);
        FB.login(function(response) {
            if (response.authResponse) {
                //If you want the user's Facebook ID or their access token, this is how you get them.
                var uid = response.authResponse.userID;
                var access_token = response.authResponse.accessToken;
                window.location = _this.find('a').attr('href');
            } else {
                $('#alert-auth').fadeIn();
                $('#alert-auth #close-button, #fb-play').click(function(){
                    $('#alert-auth').fadeOut();
                });
                return false;
            }
        }, {scope: APP_SCOPE});
        return false;
    });
    
    /**** Connect FB in popup */
    /*$('#fb-connect a').click(function(e){
        e.preventDefault();
        window.open(this.href,'', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=300,width=550');
    });*/

    (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/fr_FR/all.js#xfbml=1";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
});