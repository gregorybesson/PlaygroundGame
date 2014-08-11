$(function(){
	/************** LIVE PHOTO UPLOAD */
	$('#postForm input[type="file"]').change(function(e){
		e.preventDefault();
		var file = this.files[0];
	    var fd = new FormData();
	    var ajaxupload =  $('.photo-file:first').attr('data-url');
	    var canonical =  $('.photo-file:first').attr('data-canonical');
	    fd.append(this.name, file);
	
	    if(this.name != ''){
	        var request = $.ajax({
	           url: ajaxupload,
	           type: "POST",
	           data: fd,
	           xhr: function() {  
	               myXhr = $.ajaxSettings.xhr();
	               if(myXhr.upload){ 
	                   myXhr.upload.addEventListener('progress',startProgress, false); // for handling the progress of the upload
	               }
	               return myXhr;
	           },
	           processData: false,
	           contentType: false,
	        });
	        
	        /*
	        var progressBar = document.querySelector('progress');	
	        function startProgress(e){
	            if(e.lengthComputable){
	        	      progressBar.value = (e.loaded / e.total) * 100;
	        	      progressBar.textContent = progressBar.value;
	            }
	        };
	        */
	       
	       
	       
	       
	       
	       
	       
	        var progressBar = $(this).parent().prev();
	        var progressText = progressBar.find('.percent b');
	        
	        function startProgress(e){
	            if(e.lengthComputable){
	            	 var progressW = parseInt((e.loaded / e.total) * 100);
	            	 progressBar.fadeIn(function(){
	            	 	progressBar.find('.bar').css('width',progressW+'%');
	        	      	progressText.text(progressW+'%');
	            	 });    
	            }
	        };
	       
	        
	       
	        request.done(function (response, textStatus, jqXHR){
	        	var jsonResponse = jQuery.parseJSON(response);
	            if(jsonResponse.success){
	            	progressBar.fadeOut(function(){
	            		progressBar.prev().empty().prepend('<img class="preview" src="'+canonical+jsonResponse.fileUrl+'" alt=""/>');
	            	});
	            }
	        });
	        
	       
	       
	       
	       
	       
	       /* //WITHOUT UNIFORM
	       
	       var progressBar = $(this).parent().find('.wrap-progress');
	       var progressText = progressBar.find('.percent b');
	        
	        function startProgress(e){
	            if(e.lengthComputable){
	            	 var progressW = (e.loaded / e.total) * 100;
	            	 progressBar.fadeIn(function(){
	            	 	progressBar.find('.bar').css('width',progressW+'%');
	        	      	progressText.text(progressW+'%');
	            	 });    
	            }
	        };
	       
	
			request.done(function (response, textStatus, jqXHR){
	        	var jsonResponse = jQuery.parseJSON(response);
	            if(jsonResponse.success){
	            	//alert(jsonResponse.fileUrl);
	            	progressBar.fadeOut(function(){
	            		//progressBar.next().hide();
	            		progressBar.prev().empty().prepend('<img class="preview" src="'+canonical+jsonResponse.fileUrl+'" alt=""/>');
	            	});
	            }
	        });
	        */
			
			
			
	
	        request.fail(function (jqXHR, textStatus, errorThrown){
	            console.error("The following error occured: "+ textStatus, errorThrown);
	        });
	    }
	});
	
	// bind trash
	/*
	$('#postForm .trash').click(function(){
		var imgUp = $(this).parent().prev();
		var progressBar = $(this).parent().next();
			imgUp.fadeOut(function(){
				progressBar.fadeIn();
			});
		});
		*/
	
});
$(function(){
	$('#container').prepend('<div id="overlay"></div><div id="wrap-popin"><div id="close"></div><div id="popin"></div></div>');
	$('#reglement a').click(function(){
		showpopin($(this));
		return false;
	});
	
	

	
});

function showpopin(link){
	$('#popin').load(link.attr('href')+' #terms',function(){
		$('#popin #terms').addClass('scroll-pane');
		
		$('.fb-target a').each(function() {
			$(this).attr('target', '_blank');
		});
	
		$('#overlay').fadeIn();
		$('#wrap-popin').fadeIn();
		//$('.scroll-pane').jScrollPane();
		$('.scroll-pane').each(function(){
			$(this).jScrollPane({
				showArrows: $(this).is('.arrow')
			});
			var api = $(this).data('jsp');
			var throttleTimeout;
			$(window).bind('resize', function(){
				if ($.browser.msie) {
					// IE fires multiple resize events while you are dragging the browser window which
					// causes it to crash if you try to update the scrollpane on every one. So we need
					// to throttle it to fire a maximum of once every 50 milliseconds...
					if (!throttleTimeout) {
						throttleTimeout = setTimeout(function(){
							api.reinitialise();
							throttleTimeout = null;
						}, 50);
					}
				} else {
					api.reinitialise();
				}
			});
		})
	});
	
	$('#close, #overlay').click(function(){
		$('#wrap-popin').fadeOut();
		$('#overlay').fadeOut();
	})
}

function getBaseURL() {
    var url = location.href;  // entire url including querystring - also: window.location.href;
    var baseURL = url.substring(0, url.indexOf('/', 14));

    if (baseURL.indexOf('http://localhost') != -1) {
        // Base Url for localhost
        var url = location.href;  // window.location.href;
        var pathname = location.pathname;  // window.location.pathname;
        var index1 = url.indexOf(pathname);
        var index2 = url.indexOf("/", index1 + 1);
        var baseLocalUrl = url.substr(0, index2);

        return baseLocalUrl + "/";
    }
    else {
        // Root Url for domain name
        return baseURL + "/";
    }
    
}

function slidemenu(binder,binded){
	$(binder).hover(function() {
	    clearTimeout($(this).data('mouseovertimer'));
	    clearTimeout($(binded).data('mouseovertimer'));
	    $(binded).slideDown('normal');
	}, function() {
	    var $this = $(this);
	    $this.data('mouseovertimer', setTimeout(function(){
	        $(binded).slideUp('normal');
	    }, 700));
	});
	$(binded).hover(function() {
	    clearTimeout($(binder).data('mouseovertimer'));
	}, function(){
	    var $this = $(this);
	    if($(binded+' input').is(':focus')){
	    	$this.data('mouseovertimer', setTimeout(function(){
		        $this.slideUp('normal');
		    }, 50000));
	    } else {
		    $this.data('mouseovertimer', setTimeout(function(){
		        $this.slideUp('normal');
		    }, 700));
		}
	});
}

function slideup(binder,binded){
	$(binder).mouseout(function(){
		$(binded).slideUp();
		return false;
	});
}

function fade(binder, binded1, binded2){
	if(typeof binded2 == undefined){binded2 = 0}
    $(binded1).css({display: 'none'});
    if(binded2 != 0){
        $(binded2).css({display: 'none'});
    }
    $(binder).click(function(){
		$(binder).hide();
        $(binded1).fadeIn().css('display','inline-block');
        if(binded2 != 0){
		    $(binded2).fadeIn().css('display','inline-block');
        }
		return false;
	});
}

function slideclick(binder,binded,firstSlide,secondSlide){
	$(binder).click(function(){
		$(firstSlide).slideUp();
		$(secondSlide).slideUp();
		$(binded).slideToggle();
		return false;
	});
}

function sliderphoto(sliderid){
	$(sliderid).nivoSlider({
        effect: 'fade', // Specify sets like: 'fold,fade,sliceDown'
        slices: 15, // For slice animations
        boxCols: 8, // For box animations
        boxRows: 4, // For box animations
        animSpeed: 500, // Slide transition speed
        pauseTime: 3000, // How long each slide will show
        startSlide: 0, // Set starting Slide (0 index)
        directionNav: true, // Next & Prev navigation
        controlNav: false, // 1,2,3... navigation
        controlNavThumbs: false, // Use thumbnails for Control Nav
        pauseOnHover: true, // Stop animation while hovering
        manualAdvance: false, // Force manual transitions
        prevText: 'Prev', // Prev directionNav text
        nextText: 'Next', // Next directionNav text
        randomStart: false, // Start on a random slide
        beforeChange: function(){}, // Triggers before a slide transition
        afterChange: function(){}, // Triggers after a slide transition
        slideshowEnd: function(){}, // Triggers after all slides have been shown
        lastSlide: function(){}, // Triggers when last slide is shown
        afterLoad: function(){} // Triggers when slider has loaded
    });
}

function postvotecount(buttonlink, buttonpostcheck, postnumber, votesnumber, buttonpost, button, votesnumberlink, alreadyvoted){
	$(buttonlink).click(function(e){
		var target = $(this);
        if(target.hasClass(buttonpostcheck)){
        	
        }else{
        	e.preventDefault();
            var nb_vote_number = parseInt($(this).parent().find(postnumber).text());
            
            var request = $.ajax({
                url:  target.attr('href'),
                type: 'POST',
                data: '',
                dataType: 'json',
            }); 

            request.done(function (response, textStatus, jqXHR){
            	
                if(response.success){
                	nb_vote_number++;
                	target.parent().find(postnumber).text(nb_vote_number);
                	target.parent().removeClass(button).addClass(votesnumber);
                	target.removeClass(buttonpost).addClass(buttonpostcheck).css({cursor: 'default'});
                } else{
                    //$(".input-login-error").text("Le nom d'utilisateur ou le mot de passe saisi est incorrect.");
                    target.parent().find(alreadyvoted).css({display: 'block'});
                }
            });

            request.fail(function (jqXHR, textStatus, errorThrown){
                console.error("The following error occured: "+ textStatus, errorThrown);
            });
        }
        return false;
    });
}

function checkFormError(form){
    var first_error = true;
    $(form + ' ul li').each(function(){
        $(this).addClass('red');
        $(this).parent().parent().parent().find('p').addClass('red');
        if(first_error){
            $('html,body').animate({
                    scrollTop: $(this).parent().parent().parent().parent().offset().top},
                'slow');
            first_error = false;
        }
    });
}

function checkPointsCoordonnees(type, selector){
    switch (type) {
        case ('input') :
            if($('input[name="'+selector+'"]').val() !== ''){
                $('input[name="'+selector+'"]').parent().next().children('p').remove();
            }
        break;
        case ('file') :
        	var avatarimg = $('#sidebar-user .avatar img').attr('src');
        	var avatarf = $('#sidebar-user .avatar').attr('data-noavatarf');
        	var avatarh = $('#sidebar-user .avatar').attr('data-noavatarh');
            if( (avatarimg !== avatarf) && (avatarimg !== avatarh) ) {
                $('input[name="'+selector+'"]').parent().parent().next().children('p').remove();
            }
        break;
        case ('select') :
            if($('select[name="'+selector+'"] option:selected').val() !== ''){
                $('select[name="'+selector+'"]').parent().parent().next().children('p').remove();
            }
        break;
    }
}

function checkPointsCoordonneesValid(){
    if($('#update-adresse .orange-points').length === 0){
        $('#motto-gagner-points-coordonnees').hide();
    }
}

function checkPointsHobbyValid() {
    if($('#update-hobby .hobbies input').is(':checked')){
    	$('#motto-gagner-points-hobby').hide();
    }
}

function isFormValid(btn) {
    if($('body').find('.check-error').length > 0) {
        $('#'+btn).find('button[type="submit"]').attr('type', 'button');
    }else{
        $('#'+btn).find('button[type="button"]').attr('type', 'submit');
    }
}
$(function(){
	
	/**************************** Header */
	/**** Menu */
	if($(window).width() >= 800){
		slidemenu('.link-connect','#slide-connexion');
		slidemenu('#user-account','#slide-connexion');
		slidemenu('#user-badge','#slide-badge');
	} else { // Responsive
		slideclick('#menu', '.barnav .mainnav', '#slide-connexion', '#slide-badge');
		slideclick('#user-account, .link-connect', '#slide-connexion', '.barnav .mainnav', '#slide-badge');
		slideclick('#user-badge', '#slide-badge', '.barnav .mainnav', '#slide-connexion');
	};
	
	
	/**** Adserving */
	if( $('#ad-top, #ad-sidebar').children().length > 0 ) {
		$('#ad-top, #ad-sidebar').addClass('ad-padding');
	}
	
	
	/**************************** Page FAQ */
	 $('#faq h3').click(function(){
	 	var toslide = $(this).next();
	 	var answers = $('#faq .content-block');
	 	if($(toslide).is(':visible')){
			$(answers).slideUp();
		} else {
			$(answers).slideUp();
			$(toslide).slideDown();
		}
		return false;
	 });
    
    
    /**************************** Colonne droite */
	/**** Leaderboard */
	$('.ranking ul .general').hide();
	$('.ranking .filter-bar .general').click(function(){
		$(this).children().addClass('active');
		$('.ranking .filter-bar .week a').removeClass('active');
		$('.ranking ul .week').hide();
		$('.ranking ul .general').show();
		return false;
	})
	$('.ranking .filter-bar .week').click(function(){
		$(this).children().addClass('active');
		$('.ranking .filter-bar .general a').removeClass('active');
		$('.ranking ul .general').hide();
		$('.ranking ul .week').show();
		return false;
	})
	
	
    /**************************** Page Gagnant */
   	/**** Slider */
    $('#winnerslider img').css('width', '568px');
   	$('#winnerslider img').css('height', 'auto');
    sliderphoto('#winnerslider');    
   	$('#winnerslider .nivo-main-image').css('width', '568px');
   	
   	
   	/**************************** Page Classement */
  	/**** Filters */
	$('#filter-leaderboard').change(function () {
        var url = $(this).val();
        if (url != '') {
            window.location = url;
        }
        return false;
    });
  
    var urloption = window.location.pathname;
	$("#filter-leaderboard option").each(function() {
	    var value = $(this).val();
	    if(urloption === value) {
	    	if($(this).text() != "Sélectionner"){
	    		$(this).attr('selected','selected');
	       		$('#uniform-filter-leaderboard span').text($(this).html());
	    	}
	    	
	    };
	});


    /**************************** Custom style Form */
    $("select, input[type=radio], input[type=checkbox]").uniform();
    
    var dataprofilfile = $('#avatarfile').attr('data-profilfile');
    $('#avatarfile input').attr('size','33');
    $("#avatarfile input[type=file]").uniform({
		fileButtonHtml: 'Parcourir...',
		fileDefaultHtml: dataprofilfile
	});
	
	/**** Sliders par défaut */
	$('.default-slider img').css('width', '568px');
   	$('.default-slider img').css('height', 'auto');
    sliderphoto('.default-slider');    
   	$('.default-slider .nivo-main-image').css('width', '568px');
   	

	/*
	$.each($('.preview-img'),function(){
		if($(this).find('.preview')){
			var valueOK = $(this).find('.preview').attr('data-value');
			$(this).next().next().find('.filename').text(valueOK);
		}
	});
	*/
	
	
	/**************************** Arrondi IE */
	/*if (window.PIE) {
        $('.rounded, .backgrey, .btn, .block-game .dark-overlay, .sidebar .tonnes-cadeaux, .sidebar .newsletter, .ranking .week a, .ranking .general a, input[type="text"], input[type="password"]').each(function() {
            PIE.attach(this);
        });
    };*/
	
});
$(function(){
	
	/**************************** Login */
	$("form#header-login").submit(function(event){
    	event.preventDefault();
        var $form = $(this);
        var inputs = $form.find("input, button");
        $(".input-login-error").text("");
        var datas = $form.serialize();
        inputs.prop("disabled", true);

        var request = $.ajax({
            url:  $(this).attr('action'),
            type: $(this).attr('method'),
            data: datas,
            dataType: 'json',
        }); 

        request.done(function (response, textStatus, jqXHR){
            if(response.success){
            	location.reload();
            } else{
                $(".input-login-error").text("Le nom d'utilisateur ou le mot de passe saisi est incorrect.");
            }
        });

        request.fail(function (jqXHR, textStatus, errorThrown){
            console.error("The following error occured: "+ textStatus, errorThrown);
        });

        request.always(function () {
            inputs.prop("disabled", false);
        });
        return false;
    });
    
    $("form#register-login").submit(function(event){
    	event.preventDefault();
        var $form = $(this);
        var inputs = $form.find("input, button");
        $(".input-login-error").text("");
        var datas = $form.serialize();
        inputs.prop("disabled", true);

        var request = $.ajax({
            url:  $(this).attr('action'),
            type: $(this).attr('method'),
            data: datas,
            dataType: 'json',
        }); 

        request.done(function (response, textStatus, jqXHR){
            if(response.success){
            	window.location = $('#login-part').attr('data-redirect');
            	//location.reload();
            } else{
                $(".input-login-error").text("Le nom d'utilisateur ou le mot de passe saisi est incorrect.");
            }
        });

        request.fail(function (jqXHR, textStatus, errorThrown){
            console.error("The following error occured: "+ textStatus, errorThrown);
        });

        request.always(function () {
            inputs.prop("disabled", false);
        });
        return false;
    });
    
    /**************************** Register */
	$('.update-login-block .validate').find('[name="newCredential"]').attr('id','password');
	$('.update-login-block .validate').find('[name="newCredential"]').addClass('security');
	$('.update-login-block .validate').find('[name="newCredentialVerify"]').attr('id','passwordVerify');
	$('.update-login-block .validate').find('[name="newCredentialVerify"]').attr('equalTo','#password');
	$('#register-form #passwordVerify').attr('equalTo','#password');
	$('#register-form #postalcode').attr('minlength',5);
	
	$('.validate').each(function () {
	    $(this).validate();
	});
	
	jQuery.validator.addMethod('security', function(value, element) {
		//min 6
		return this.optional(element) || /(?=.*[a-z]).{6,20}/.test(value);
	}, 'Niveau de sécrurité : Faible');  
	
	$('#password').keyup(function(){
        if($(this).val().match(/(?=.*[a-z]).{6,20}/)){
           setTimeout(function(){
            	 $('#password').parent().addClass('valid-form');
           });
        }
        else{
        	setTimeout(function(){
           		 $('#password').parent().removeClass('valid-form');
           });
        }
    });    
	
	$('#passwordVerify').keyup(function(){
		if($('#password').val() == $('#passwordVerify').val()){
			setTimeout(function(){
				$('#passwordVerify').parent().addClass('valid-form');
			});
		}else{
			setTimeout(function(){
				$('#passwordVerify').parent().removeClass('valid-form');
			});
		}
	});
	
	$('.validate input').keyup(function(){
		$(this).parent().removeClass('valid-form');
		if($(this).hasClass('valid')){
			$(this).parent().addClass('valid-form');
		}
		else{
			$(this).parent().removeClass('valid-form');
		}
		if($(this).val()== ''){
			$(this).parent().removeClass('valid-form');
		}
	});
	
	/**************************** Profile */
	/**** Form */
	jQuery.validator.addMethod('phonefr', function(value, element) {
		return this.optional(element) || /^(01|02|03|04|05|06|08)[0-9]{8}/.test(value);
	}, 'Le numéro n\'est pas valide.');
	$('#update-adresse .phone input').keyup(function(){
        if($(this).val().match(/^(01|02|03|04|05|06|08)[0-9]{8}/)){
           setTimeout(function(){
            	 $('#update-adresse .phone input').parent().addClass('valid-form');
           });
        }
        else{
        	setTimeout(function(){
           		 $('#update-adresse .phone input').parent().removeClass('valid-form');
           });
        }
    });
    
    jQuery.validator.addMethod('zipcodefr', function(value, element) {
		return this.optional(element) || /^(([0-8][0-9])|(9[0-5]))[0-9]{3}$/.test(value);
	}, 'Le numéro n\'est pas valide.');
	$('#update-adresse .zipcode input').keyup(function(){
        if($(this).val().match(/^(01|02|03|04|05|06|08)[0-9]{8}/)){
           setTimeout(function(){
            	 $('#update-adresse .zipcode input').parent().addClass('valid-form');
           });
        }
        else{
        	setTimeout(function(){
           		 $('#update-adresse .zipcode input').parent().removeClass('valid-form');
           });
        }
    });
	
	//delete account confirmation
	fade('#delete-account #del-confirm','#delete-account #del-input', '#delete-account #del-cancel');

	//success sending password (lostpass page)
	fade('#lost-submit','#response');
	
	/**** Form validation */
    checkFormError('#update-adresse');
    checkFormError('#update-login');
    //checkPointsCoordonnees('input', 'username');
    checkPointsCoordonnees('file', 'avatar');
    checkPointsCoordonnees('input', 'lastname');
    checkPointsCoordonnees('input', 'firstname');
    checkPointsCoordonnees('input', 'address');
    checkPointsCoordonnees('input', 'postal_code');
    checkPointsCoordonnees('input', 'city');
    checkPointsCoordonnees('input', 'telephone');
    checkPointsCoordonnees('select', 'birth_year');
    checkPointsCoordonnees('select', 'children');
    checkPointsCoordonnees('select', 'contact-question');    
    checkPointsCoordonnees('input', 'optin');
    checkPointsCoordonneesValid();
    checkPointsHobbyValid();
	
	
	/**************************** Module Newsletter */
	$("form#ajax-newsletter").submit(function(event){
    	event.preventDefault();
        var $form = $(this);
        var inputs = $form.find("input, button");
        $(".input-login-error").text("");
        var datas = $form.serialize();
        inputs.prop("disabled", true);

        var request = $.ajax({
            url:  $(this).attr('action'),
            type: $(this).attr('method'),
            data: datas,
            dataType: 'json',
        }); 

        request.done(function (response, textStatus, jqXHR){
            if(response.success){
            	$('#ajax-newsletter .btn').hide();
            	$('.bounce-newsform').hide();
           		$('.newsletter-connect .suscribe-success').fadeIn();
           		$('.bounce-newssuccess').fadeIn();
            } else{
                //$(".input-login-error").text("Le nom d'utilisateur ou le mot de passe saisi est incorrect.");
            }
        });

        request.fail(function (jqXHR, textStatus, errorThrown){
            console.error("The following error occured: "+ textStatus, errorThrown);
        });

        request.always(function () {
            inputs.prop("disabled", false);
        });
        return false;
    });
    
  	
  	/**************************** Activity */
  	/**** Filters */
	$('#filter-activity').change(function () {
        var url = $(this).val();
        if (url != '') {
            window.location = url;
        }
        return false;
    });
  
    var urloption = window.location.pathname;
	$("#filter-activity option").each(function() {
	    var value = $(this).val();
	    if(urloption === value) {
	    	if($(this).text() != "Vue d'ensemble"){
	    		$(this).attr('selected','selected');
	       		$('#uniform-filter-activity span').text($(this).html());
	    	}
	    	
	    };
	});
	
	/**** load more activity */
	$('#more-activity').click(function(){
		 var url = $(this).find('a').attr('href');
		 var incr = parseInt($(this).find('a').attr('data-incr'));
		 var total = parseInt($(this).find('a').attr('data-total'));
		 var viewTotal = $('.date').size();
		 
		 if(total > viewTotal){
			 $(this).prev().append('<div class="load-activity"></div>');
			 $('.load-activity:last').load(url+incr+' #list-activity' , function(){
			 	$(this).find('#list-activity').removeAttr('id');
			 	$('#more-activity a').attr('data-incr',incr+1);
			 });
		 }
		 else {
		 	$(this).fadeOut();
		 }
		 return false;
	});
	
    
    /**** REGISTER FORM LIVE CHECK */
   	/*
    checkLiveFormError('input', 'lastname', 'Veuillez renseigner votre nom.', 'btn-create-account');
    checkLiveFormError('input', 'firstname', 'Veuillez renseigner votre prénom.', 'btn-create-account');
    checkLiveFormError('input', 'email', 'Veuillez renseigner votre email.', 'btn-create-account');
    checkLiveFormError('input', 'password', 'Veuillez renseigner un mot de passe.', 'btn-create-account');
    checkLiveFormError('password', 'passwordVerify', 'Veuillez confirmez votre mot de passe.', 'btn-create-account', 'password');
    checkLiveFormError('select', 'birth_year', 'Veuillez renseigner votre année de naissance.', 'btn-create-account');
    checkLiveFormError('input', 'postal_code', 'Veuillez renseigner votre code postal.', 'btn-create-account');
	*/
    
});
$(function(){

	/**************************** Homepage */
	
	var APP_ID =  $('meta[property="fb:app"]').attr('content');
	var BIT_CLIENT = $('meta[property="bt:client"]').attr('content');
	var BIT_USER = $('meta[property="bt:user"]').attr('content');
	var BIT_KEY = $('meta[property="bt:key"]').attr('content');
	
	
	/**** Result variables */
	var dataUrl 		= $('#datas-result').attr('data-url');
	var dataRoute 		= $('#datas-result').attr('data-route');
	var dataSecretKey 	= $('#datas-result').attr('data-secretkey');
	var dataFbMsg 		= $('#datas-result').attr('data-fbmsg');
	var dataTwMsg 		= $('#datas-result').attr('data-twmsg');
	var imgFb 			= $('meta[property="og:image"]').attr('content');
	var dataFbRequest 	= $('#datas-result').attr('data-fbrequest');
	var dataSocialLink 	= $('#datas-result').attr('data-sociallink');
	
	
	//$.getScript("http://platform.twitter.com/widgets.js");
	
	
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
	
	
	/**** Shorturl Twitter */
	$('.shorturl').mouseenter(function(){
		var txt = $(this).attr('data-txt');
		var url = $(this).attr('data-url');
		
		var shorturl = '';
	    $.getJSON(
	        BIT_CLIENT+"?", 
	        { 
	        	"login": BIT_USER,
	        	"apiKey": BIT_KEY,
	        	"longUrl": url,
	            "format": "json"
	        },
	        function(response)
	        {
	            shorturl = encodeURIComponent(response.data.url);
	            $('.shorturl').attr('href','https://twitter.com/intent/tweet?text='+txt+'&url='+shorturl);
	        }
	    );
	});

	
	/**** Social share */
    $(".grey-fb,.dark-fb").click(function(event){
    	event.preventDefault();
    	var link = $(this).attr('data-link');
    	var picture = $(this).attr('data-picture');
    	var caption = $(this).attr('data-caption');
    	var description = link;
    	displayFBUI(link,picture,caption,description);
	});
	
	
	/**** Facebook share - Result page */
	$("#fb-share").click(function(event){
    	event.preventDefault();
		var data=
		{
			method: 'feed',
			message: "",
			display: 'iframe',
			name: "Plateforme Playground",
			caption: dataFbMsg,
			description: dataUrl,
			picture: imgFb,
			link: dataSocialLink,
			//actions: [{ name: 'action_links text!', link: 'http://www.google.com' }],
		};
		FB.getLoginStatus(function(response) {
		      if (response.status === 'connected') {
		          //If you want the user's Facebook ID or their access token, this is how you get them.
		          var uid = response.authResponse.userID;
		          var access_token = response.authResponse.accessToken;
		          FB.ui(data, onFbPostCompleted);
		      } else {
		          //If they haven't, call the FB.login method
		          FB.login(function(response) {
		              if (response.authResponse) {
		                  //If you want the user's Facebook ID or their access token, this is how you get them.
		                  var uid = response.authResponse.userID;
		                  var access_token = response.authResponse.accessToken;
		                  FB.ui(data, onFbPostCompleted);
		              } else {
		                  //alert("You must install the application to share your greeting.");
		              }
		          }, {scope: 'publish_stream'});
		      }
		  });
	});
	
	/**** Facebook invit - Bounce page */
	$("#fb-request").click(function(event){
    	event.preventDefault();
		var data=
		{
			method: 'apprequests',
			message: dataFbRequest,
		}
		FB.getLoginStatus(function(response) {
			if (response.status === 'connected') {
		    	//If you want the user's Facebook ID or their access token, this is how you get them.
		        var uid = response.authResponse.userID;
		        var access_token = response.authResponse.accessToken;
		        FB.ui(data, onFbRequestCompleted);
		    } else {
		        //If they haven't, call the FB.login method
		        FB.login(function(response) {
		        	if (response.authResponse) {
		            	//If you want the user's Facebook ID or their access token, this is how you get them.
		                var uid = response.authResponse.userID;
		                var access_token = response.authResponse.accessToken;
		                FB.ui(data, onFbRequestCompleted);
		            } else {
		                //alert("You must install the application to share your greeting.");
		            }
		        }, {scope: 'publish_stream'});
		    }
		});
	});
	
	var oneshare = true;
	
	$("#google-plus").click(function(e){
		e.preventDefault();
		var gplus = window.open(this.href,'', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=300,width=550');
		
		if(oneshare){
			if(dataRoute){
				var url = dataRoute;
			}
			else{
				var url = dataUrl;
			}
			var timer = setInterval(function() {   
			    if(gplus.closed) {  
			        clearInterval(timer); 
			        var request = $.ajax({
			            url: url + '/google?googleId=' + dataSecretKey,
			            type: 'GET',
			        }); 
			        oneshare = false;
			    }  
			}, 1000); 
		}
	});
	
	
	/**** Facebook */
	function displayFBUI(link,picture,caption,description) {
		FB.ui({
	        method: 'feed',
		    name: 'Playground',
		    link: link,
		    picture: picture,
		    caption: caption,
		    description: description
    	});
	}

	(function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) return;
		js = d.createElement(s); js.id = id;
		js.src = "//connect.facebook.net/fr_FR/all.js#xfbml=1";
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));
	
	// Load the SDK's source Asynchronously
	// Note that the debug version is being actively developed and might
	// contain some type checks that are overly strict.
	// Please report such bugs using the bugs tool.
	//(function(d, debug){
	//	var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
	//    if (d.getElementById(id)) {return;}
	//    js = d.createElement('script'); js.id = id; js.async = true;
	//    js.src = "//connect.facebook.net/fr_FR/all" + (debug ? "/debug" : "") + ".js";
	//    ref.parentNode.insertBefore(js, ref);
	//}(document, /*debug*/ false));
	
	function onFbPostCompleted(response){
		if (response){
			//console.log(response);
			if (!response.error){
				if (response.id || response.post_id){
					if(dataRoute){
						var url = dataRoute;
					}
					else{
						var url = dataUrl;
					}
					var request = $.ajax({
			            url: url + '/fbshare?fbId=' + dataSecretKey,
			            type: 'GET',
			        });
				}
			}
		}
		// user cancelled
	}
	
	function onFbRequestCompleted(response) {
		if (response) {
			//console.log(response);
			if (response.error) {
				alert(response.error.message);
			}
			else {
				if (response.request)
					var request = $.ajax({
			            url: dataUrl + '/fbrequest?fbId=' + dataSecretKey,
			            type: 'GET',
			        });
				else
					alert(JSON.stringify(response));
			}
		}
		// user cancelled
	}
	
	
	/**** Google+ */
	function onGooglePlus(response){
		if (response){
			//console.log(response);
			if (!response.error){
				if (response.state){
					var request = $.ajax({
			            url: dataUrl + '/google?googleId=' + dataSecretKey,
			            type: 'GET',
			        });
			        //alert('ok');
				}
			}
		}
		// user cancelled
	}
	
	(function() {
	    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
	    po.src = 'https://apis.google.com/js/plusone.js';
	    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
	})();
	
	/**** Twitter */
	$.getScript("http://platform.twitter.com/widgets.js", function(){
		function handleTweetEvent(event){
	    	if (event) {
	    		if(dataRoute){
					var url = dataRoute;
				}
				else{
					var url = dataUrl;
				}
	    		var request = $.ajax({
	            	url: url + '/tweet?tweetId=' + dataSecretKey,
	            	type: 'GET',
	         	});
	         	
	     	}
	   	}
	   	twttr.events.bind('tweet', handleTweetEvent);
	});
	
});
$(function(){
	
	/**************************** Homepage - Slider */
    $('.carousel').carousel({
        pause: ""
    });
    
    $('.carousel-inner .item:first').addClass('active');
    
    
    /**************************** Facebook : target:_blank */
	$('.fb-target a').each(function() {
		$(this).attr('target', '_blank');
	}); 
    
	/**************************** Game - Instant gagnant */
	/**** Grattage */
	if($(window).width() > 550){
		$("#wScratchgame").wScratchPad({
			width   : 211,
	    	height  : 166,
	    	size 	: 15,
	    	image2  : $('#wScratchgame').attr('data-scratchthis'),
	    	color 	: '#fff',
			overlay : 'none',
			firsttext 	: $('#wScratchgame div').attr('data-firsttxt'),
			middletext 	: $('#wScratchgame div').attr('data-middletxt'),
			lasttext 	: $('#wScratchgame div').attr('data-lasttxt'),
			classscratch : $('#wScratchgame div').attr('class'),
			scratchDown: function(e, percent){$("#wScratchgame").attr('data-percentscratched', percent)},
	        scratchMove: function(e, percent){$("#wScratchgame").attr('data-percentscratched', percent)},
	        scratchUp: function(e, percent){$("#wScratchgame").attr('data-percentscratched', percent)}
		});
	} else {
		$("#wScratchgame").wScratchPad({
			width   : 140,
	    	height  : 90,
	    	size 	: 10,
	    	image2  : $('#wScratchgame').attr('data-scratchthismobile'),
	    	color 	: '#fff',
			overlay : 'none',
			firsttext 	: $('#wScratchgame div').attr('data-firsttxt'),
			middletext 	: $('#wScratchgame div').attr('data-middletxt'),
			lasttext 	: $('#wScratchgame div').attr('data-lasttxt'),
			classscratch : $('#wScratchgame div').attr('class'),
			scratchDown: function(e, percent){$("#wScratchgame").attr('data-percentscratched', percent)},
	        scratchMove: function(e, percent){$("#wScratchgame").attr('data-percentscratched', percent)},
	        scratchUp: function(e, percent){$("#wScratchgame").attr('data-percentscratched', percent)}
		});
	};
	
	/**** Temps d'affichage du résulat */
	$('#wScratchgame canvas').mousedown(function(){
		$('#wScratchgame .scratchcontent').show();
	})
	
	/**** Navigation - Mécanique */
	$('#play-instantwin .btn a').bind('click', false);
	$("#wScratchgame canvas").mouseup(function(){
		var sp = $("#wScratchgame").attr('data-percentscratched');
		if(sp >= 10){
			$('#play-instantwin .btn').removeClass('btn-warning-inactive');
	    	$('#play-instantwin .btn').addClass('btn-warning');
	    	$('#play-instantwin .btn a').unbind('click', false);
	    	
	    	$('#play-instantwin .btn-warning.success').click(function(){
				$('#play-instantwin').hide();
				$('html, body').animate({ scrollTop: 0 }, 0);
				$('#result-instantwin').fadeIn();
				return false;
			});
	    }
	});
	
	
	/**************************** Game - Post & vote */
	$('.alert-link').click(function(){
		$(this).parent().submit();
		return false;
	});
	
    /**** Sliders Photo contest */
    var countslider = $('.nivoSlider').size();
	$.each($('.nivoSlider'),function(){
		 sliderphoto(this);
	});
	for(var i=0 ; i<=countslider ; i++){
        sliderphoto('#slider'+i);
    }
    
    /**** Style input file */
    $('#photokitchen-form input[type=file]').uniform({
		fileButtonHtml: 'Parcourir...',
		fileDefaultHtml: 'Photo'
	});
	
	$(".photo-file input[type=file]").uniform({
		fileButtonHtml: 'Parcourir...',
		fileDefaultHtml: 'Photo'
	});
	
	var photoFile = $('.game-postvote .photo-file').size();
	if(photoFile <= 1){
		$('.picto .star').hide();
		PostVoteInput('.game-postvote .photo-file input:file', '.game-postvote .photo-file .filename', '.game-postvote .photo-file .picto');
	} else {
		var i=1;
		$.each($('.game-postvote .photo-file'),function(){
			$(this).addClass('input'+i);
			PostVoteInput('.input'+i+' input:file', '.input'+i+' .filename', '.input'+i+' .picto');
			i++;
		});
	}
	
	function PostVoteInput(inputfile, filename, picto){
		var ivalue = $(inputfile).attr('value');
		if (ivalue != undefined){
			var isplitted = ivalue.split('/');
			var ilast = '';
			if (isplitted.length > 0) {
				ilast = isplitted[isplitted.length-1];
			}
			$(filename).html(ilast);
		}
		if ($(filename).html() == 'Photo'){
			$(picto).hide();
		}
	}
	
	
   	/**** Count characters form */
   	$('#photomsg').limiter('400','#counter-photomsg');
   	
   	$.each($('.form-textarea textarea'), function(){
   		var maxlength = $(this).attr('maxlength');
   		var charleft = $(this).parent().next().find('.character-left');
   		if(typeof maxlength !== 'undefined'){
   			charleft.text(maxlength);
   			charleft.parent().fadeIn();
   		}
   		$(this).limiter(maxlength, charleft);
   	});
   	
   	
   	/**** Vote Ajax */
    postvotecount('.nb-votes a.logged', 'btn-post-vote-check', '.nb-post-vote-number', 'nb-votes-check', 'btn-post-vote', 'nb-votes', 'nb-votes-check a', '.already-voted');
    
    /**** LIVE PHOTO UPLOAD */
    $('#photocontest-create-form input[type="file"]').change(function(){
        var filename = $(this).val();
        $('#uploadform').append('<input name="file" type="file" value="' + filename + '">');
        $('#uploadform-id').val($(this).parent().parent().find('.uploadphotoid').val());
        $('#uploadform').submit();
        $(this).hide();
    });
        
    
    /**************************** Game - Quizz */
    /**** Navigation - Mécanique */
	$('.game-quiz .page').first().addClass('active');
	$('.end').hide();
	if($('.page:first').hasClass('active')){
		$('.previous').hide();
	}
	if($('.page').last().hasClass('active')){
		$('.next').hide();
		$('.end').show();
	}
	$('#next').click(function() {
		var idfirst = $('.game-quiz .page.active').attr('id');
		$('#'+idfirst).removeClass('active');
		$('#'+idfirst).next('.page').addClass('active');
		if($('.page').last().hasClass('active')){
			$('.next').hide();
			$('.end').show();
		}
		$('.previous').show();
	});
	$('#previous').click(function() {
		var idfirst = $('.game-quiz .page.active').attr('id');
		$('#'+idfirst).removeClass('active');
		$('#'+idfirst).prev('.page').addClass('active');
		if($('.page:first').hasClass('active')){
			$('.previous').hide();
		}
		$('.next').show();
		$('.end').hide();
	});
	
	/**** Timer */
	$(document).ready(function () {
		var Timerquiz = new (function() {
		    var $countdown,
		        incrementTime = 70,
		        currentTime = parseInt($('.timer').text()),
		        updateTimer = function() {
		            $countdown.html(formatTime(currentTime));
		            if (currentTime == 0) {
		                Timerquiz.Timer.stop();
		                timerComplete();
		                return;
		            }
		            currentTime -= incrementTime / 10;
		            if (currentTime < 0) currentTime = 0;
		        },
		        timerComplete = function() {		            
		            alert('Le temps imparti est écoulé !');
		            // caution : the submit button in the form HAVE TO be different from "name"
		            // see : http://bugs.jquery.com/ticket/4652
		            $('form:first').submit();
		        },
		        init = function() {
		            $countdown = $('.timer');
		            Timerquiz.Timer = $.timer(updateTimer, incrementTime, true);
		        };
		    $(init);
		}); 
	});
	
	function pad(number, length) {
	    var str = '' + number;
	    while (str.length < length) {str = '0' + str;}
	    return str;
	}
	function formatTime(time) {
	    var min = parseInt(time / 6000),
	        sec = parseInt(time / 100) - (min * 60),
	        hundredths = pad(time - (sec * 100) - (min * 6000), 2);
	    return (min > 0 ? pad(min, 2) : "00") + ":" + pad(sec, 2) + ":" + hundredths;
	}
	
	/**************************** Commun : Envoi mail */
    $('.more-invit').click(function(){
    	$('#mail-send input').attr('value', '');
		$(this).parent().fadeOut(function(){
	  		$('#mail-send').fadeIn();
	  	});
  	});
	
});