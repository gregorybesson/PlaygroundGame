$(function(){
	
	if($('#dz-root').size() > 0) {
		var loadSongOnStart = $('input[name=audio]').val();
		var isAutoplay = $('input[name=autoplay]').val();

	    function addSource(elem, path) {  
	    	$('<source>').attr('src', path).appendTo(elem);  
	    }

	    function addAudioInformations(ui) {
	        if($('#audio-informations').length != 0) {
	        	$('#audio-informations').remove();
	        }
	        var divAudioInformations = $('<div>', {
	            id: 'audio-informations'
	        });
	        
			var imgCover = $('<img>', {
				src: ui.item.cover,
				style: 'margin: 10px 0px'
			});
			imgCover.appendTo(divAudioInformations);
			$('<p> Album : ' + ui.item.album + '</p>').appendTo(divAudioInformations);
			$('<p> Artist : ' + ui.item.artist + '</p>').appendTo(divAudioInformations);
			$('<p> Song : ' + ui.item.value + '</p>').appendTo(divAudioInformations);
			var audio = $('<audio>', {  
				controls : 'controls',
				id : 'audio-player'
			});
			addSource(audio, ui.item.preview);
			audio.appendTo(divAudioInformations);
			
			var labelAutoplay = $('<label for="autoplay"> Autoplay </label>');
			
			var autoPlay = $('<input>', {
				id: 'check-autoplay',
				type: 'checkbox'
			});
		
			autoPlay.change(function() {
		    	if(this.checked) {
		    		$('input[name=autoplay]').val(1);
		    	} else {
		    		$('input[name=autoplay]').val(0);
		    	}
			});
			
			autoPlay.appendTo(labelAutoplay);
			labelAutoplay.appendTo(divAudioInformations);
			
			divAudioInformations.appendTo($('#search_sound').parent()  );     
	    }

	    function getAudioInformations(id) {
	        DZ.api('/track/'+id+'', function(response) {
	        	if(response.album === undefined) {
	        		return ;
	        	}
	            var divAudioInformations = $('<div>', {
	                id: 'audio-informations'
	            });
	            
	    		var imgCover = $('<img>', {
	    			src: response.album.cover,
	    			style: 'margin: 10px 0px'
	    		});
	    		imgCover.appendTo(divAudioInformations);
	    		$('<p> Album : ' + response.album.title + '</p>').appendTo(divAudioInformations);
	    		$('<p> Artist : ' + response.artist.name + '</p>').appendTo(divAudioInformations);
	    		$('<p> Song : ' + response.title + '</p>').appendTo(divAudioInformations);
	    		var audio = $('<audio>', {  
	    			controls : 'controls',
	    			id : 'audio-player'
	    		});
	    		addSource(audio, response.preview);
	    		audio.appendTo(divAudioInformations);
	    		
	    		var labelAutoplay = $('<label for="autoplay"> Autoplay </label>');
				
				var autoPlay = $('<input>', {
					id: 'check-autoplay',
					type: 'checkbox',
				});
				
				if(isAutoplay) {
					autoPlay.attr('checked', 'checked');
				}
			
				autoPlay.change(function() {
			    	if(this.checked) {
			    		$('input[name=autoplay]').val(1);
			    	} else {
			    		$('input[name=autoplay]').val(0);
			    	}
				});
				
				autoPlay.appendTo(labelAutoplay);
				labelAutoplay.appendTo(divAudioInformations);

	    		divAudioInformations.appendTo($('#search_sound').parent()  );
	        });
	    }
	        	
	    	
	    $('#search_sound').autocomplete({
	    	source: function(request, response){
	    		$.ajax({
	    			type: 'GET',
	    			url: 'https://api.deezer.com/search/track/?q=' + request.term + '&index=0&limit=10&output=jsonp',
	    			dataType: 'jsonp',
	    			success: function (result) {
	    				response($.map(result.data, function(item) {
	    					return {
	    						label: item.title + ' - ' + item.artist.name,
	    						value: item.title,
	    						preview: item.preview,
	    						cover: item.album.cover,
	    						album: item.album.title,
	    						id: item.id,
	    						artist: item.artist.name
	    					}
	    				}));
	    			}
	    		});
	    	},
	    	minLength: 3,
	    	select: function(event, ui) {
	        	$('input[name=audio]').val(ui.item.id);
	    		addAudioInformations(ui);
	    	}
	    });

	    if(loadSongOnStart != 0 && loadSongOnStart != '') {
	        getAudioInformations(loadSongOnStart);
	    }
	}
});