CKEDITOR.addTemplates( 'default',
{
	// The name of the subfolder that contains the preview images of the templates.
	imagesPath : CKEDITOR.getUrl( '/js/ckeditor-custom/images/' ),
 
	// Template definitions.
	templates :
		[
			{
				title: 			'Game : Bienvenue',
				description: 	'Template des pages d\'accueil des jeux',
				image: 			'template1.jpg',
				html:
					'<h2>Sous-titre de l\'article</h2>' +
					'<h3>Titre de 3ème niveau</h3>' +
					'<p>Votre texte</p>' +
					'<ul><li>Votre liste à puces</li></ul>' +
					'<p>Votre texte</p>'
			},
			{
				title: 			'Game : Règlement',
				description: 	'Template des pages de règlement d\'un jeu',
				image: 			'template2.jpg',
				html:
					'<h2>Sous-titre du règlement</h2>' +
					'<h3>Article 1</h3>' +
					'<p>Texte de l\'article 1</p>' +
					'<h3>Article 2</h3>' +
					'<p>Texte de l\'article 2</p>' +
					'<h3>Article 3</h3>' +
					'<p>Texte de l\'article 3</p>' +
					'<h3>Article 4</h3>' +
					'<p>Texte de l\'article 4</p>'
			},
			{
				title: 			'Article : Les gagnants',
				description: 	'Template des pages Les gagnants',
				image: 			'template3.jpg',
				html:
					'<p><samp>Chapô de l\'article</samp></p>' +
					'<h2>Sous-titre de l\'article</h2>' +
					'<h3>Titre de 3ème niveau</h3>' +
					'<p>Votre texte</p>' +
					'<ul><li>Votre liste à puces</li></ul>' +
					'<p>Votre texte</p>' +
					'<p class="citation">Votre citation sera entre des guillemets</p>' +
					'<p class="video">Votre vidéo : insérer le code d\'intégration dans le code source</p>' +
					'<div id="winnerslider" style="text-align:center">Votre slider : insérer les images les unes à la suite des autres</div>'
			}
		]
});