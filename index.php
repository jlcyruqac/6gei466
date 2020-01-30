<?php
/* 
	Title: SmallCMS
	Descr: Mini gestionnaire de contenu base sur flasklike
	Author: Jean-Luc Cyr
	Date: 2019/10/18
*/
require('flasklike.php');

////////////////////////////////////////////////////////////////
// Page d'accueil
$route_defs['/']['GET'] = 
function(){
	$params = ['message'=>'Veuillez vous identifier',
				];
	fl_render_template('index.html', $params);
};

////////////////////////////////////////////////////////////////
// Tentative de connexion
$route_defs['/']['POST'] = 
function(){
	$params = ['fname'=>$_POST['user'],
				'lname'=>$_POST['pass'],
				];
	if (fl_auth($_POST['user'], $_POST['pass'])) {
		fl_redirect('/menu');
		$params['message'] = "Login OK";
		fl_render_template('index.html', $params);
	} else {
		$params['message'] = "Informations invalides";
		fl_render_template('index.html', $params);
	}
};

////////////////////////////////////////////////////////////////
// Menu principal
$route_defs['/menu']['GET'] = 
function(){
	if (!fl_auth()) {
		$params = ['message'=>'Veuillez vous identifier',
					];
		fl_render_template('index.html', $params);		
	} else {
		fl_render_template('menu.html', $params);				
	}
};


////////////////////////////////////////////////////////////////
// and after all definitions should call flasklike_run()
fl_run();
