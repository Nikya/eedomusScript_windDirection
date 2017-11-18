<?php

/*******************************************************************************
* Un script pour la box domotique eedomus pour convertir la direction du vent
********************************************************************************
* Version :
*	2.0
*
* Auteur :
*	Nikya
*	https://github.com/Nikya/
*
* Documentation complète et aide :
* 	https://github.com/Nikya/eedomusScript_windDirection
*
* Param :
*	periph : L'identifiant (Code API) du periferique mesurant le vent
*
* Retour :
* 	XML : Résultat formaté au format XML
*
*******************************************************************************/

// Seulement utile en mode test
//require_once ("../eedomusScriptsEmulator.php");

////////////////////////////////////////////////////////////////////////////////
// Definition des 16x2 points cardinaux
// En version courte/longue Français/Anglais
$cardinalArray = array(
	'fr' => array(
		'short' => array(
			array('N', '', ''),
			array('N', 'N', 'E'),
			array('N', 'N', 'E'),
			array('N', 'E', ''),
			array('N', 'E', ''),
			array('E', 'N', 'E'),
			array('E', 'N', 'E'),
			array('E', '', ''),
			array('E', '', ''),
			array('E', 'S', 'E'),
			array('E', 'S', 'E'),
			array('S', 'E', ''),
			array('S', 'E', ''),
			array('S', 'S', 'E'),
			array('S', 'S', 'E'),
			array('S', '', ''),
			array('S', '', ''),
			array('S', 'S', 'O'),
			array('S', 'S', 'O'),
			array('S', 'O', ''),
			array('S', 'O', ''),
			array('O', 'S', 'O'),
			array('O', 'S', 'O'),
			array('O', '', ''),
			array('O', '', ''),
			array('O', 'N', 'O'),
			array('O', 'N', 'O'),
			array('N', 'O', ''),
			array('N', 'O', ''),
			array('N', 'N', 'O'),
			array('N', 'N', 'O'),
			array('N', '', ''),
		),
		'long' => array(
			array('Nord', '', ''),
			array('Nord', '-Nord', '-Est'),
			array('Nord', '-Nord', '-Est'),
			array('Nord', '-Est', ''),
			array('Nord', '-Est', ''),
			array('Est', '-Nord', '-Est'),
			array('Est', '-Nord', '-Est'),
			array('Est', '', ''),
			array('Est', '', ''),
			array('Est', '-Sud', '-Est'),
			array('Est', '-Sud', '-Est'),
			array('Sud', '-Est', ''),
			array('Sud', '-Est', ''),
			array('Sud', '-Sud', '-Est'),
			array('Sud', '-Sud', '-Est'),
			array('Sud', '', ''),
			array('Sud', '', ''),
			array('Sud', '-Sud', '-Ouest'),
			array('Sud', '-Sud', '-Ouest'),
			array('Sud', '-Ouest', ''),
			array('Sud', '-Ouest', ''),
			array('Ouest', '-Sud', '-Ouest'),
			array('Ouest', '-Sud', '-Ouest'),
			array('Ouest', '', ''),
			array('Ouest', '', ''),
			array('Ouest', '-Nord', '-Ouest'),
			array('Ouest', '-Nord', '-Ouest'),
			array('Nord', '-Ouest', ''),
			array('Nord', '-Ouest', ''),
			array('Nord', '-Nord', '-Ouest'),
			array('Nord', '-Nord', '-Ouest'),
			array('Nord', '', ''),
		)
	),
	'en' => array(
		'short' => array(
			array('N', '', ''),
			array('N', 'N', 'E'),
			array('N', 'N', 'E'),
			array('N', 'E', ''),
			array('N', 'E', ''),
			array('E', 'N', 'E'),
			array('E', 'N', 'E'),
			array('E', '', ''),
			array('E', '', ''),
			array('E', 'S', 'E'),
			array('E', 'S', 'E'),
			array('S', 'E', ''),
			array('S', 'E', ''),
			array('S', 'S', 'E'),
			array('S', 'S', 'E'),
			array('S', '', ''),
			array('S', '', ''),
			array('S', 'S', 'W'),
			array('S', 'S', 'W'),
			array('S', 'W', ''),
			array('S', 'W', ''),
			array('W', 'S', 'W'),
			array('W', 'S', 'W'),
			array('W', '', ''),
			array('W', '', ''),
			array('W', 'N', 'W'),
			array('W', 'N', 'W'),
			array('N', 'W', ''),
			array('N', 'W', ''),
			array('N', 'N', 'W'),
			array('N', 'N', 'W'),
			array('N', '', ''),
		),
		'long' => array(
			array('North', '', ''),
			array('North', '-North', '-East'),
			array('North', '-North', '-East'),
			array('North', '-East', ''),
			array('North', '-East', ''),
			array('East', '-North', '-East'),
			array('East', '-North', '-East'),
			array('East', '', ''),
			array('East', '', ''),
			array('East', '-South', '-East'),
			array('East', '-South', '-East'),
			array('South', '-East', ''),
			array('South', '-East', ''),
			array('South', '-South', '-East'),
			array('South', '-South', '-East'),
			array('South', '', ''),
			array('South', '', ''),
			array('South', '-South', '-West'),
			array('South', '-South', '-West'),
			array('South', '-West', ''),
			array('South', '-West', ''),
			array('West', '-South', '-West'),
			array('West', '-South', '-West'),
			array('West', '', ''),
			array('West', '', ''),
			array('West', '-North', '-West'),
			array('West', '-North', '-West'),
			array('North', '-West', ''),
			array('North', '-West', ''),
			array('North', '-North', '-West'),
			array('North', '-North', '-West'),
			array('North', '', ''),
		)
	)
);

////////////////////////////////////////////////////////////////////////////////
// Lecture du paramêtre du script
$periphId = getArg('periph', true);

////////////////////////////////////////////////////////////////////////////////
// Lecture de la valeur en ° du capteur
$aVal = getValue($periphId);
$value = $aVal['value'];
$change = $aVal['change'];

////////////////////////////////////////////////////////////////////////////////
// Recherche de la division correspondante
$step = 360/32;
$divId = 0;

for ($d=0; $d<=360; $d+=$step) {
	if ($value>=$d and $value<=$d+$step)
		break;
	$divId++;
}

////////////////////////////////////////////////////////////////////////////////
// Recherche du point cardinal correspondant à la division trouvée
// dans un tableau de type [langue][longeur][val_precision_0][val_precision_1][val_precision_2]
$cardinal_fr_s = $cardinalArray['fr']['short'][$divId][0].$cardinalArray['fr']['short'][$divId][1].$cardinalArray['fr']['short'][$divId][2];
$cardinal_fr_l = $cardinalArray['fr']['long'][$divId][0].$cardinalArray['fr']['long'][$divId][1].$cardinalArray['fr']['long'][$divId][2];
$cardinal_en_s = $cardinalArray['en']['short'][$divId][0].$cardinalArray['en']['short'][$divId][1].$cardinalArray['en']['short'][$divId][2];
$cardinal_en_l = $cardinalArray['en']['long'][$divId][0].$cardinalArray['en']['long'][$divId][1].$cardinalArray['en']['long'][$divId][2];

////////////////////////////////////////////////////////////////////////////////
// Formatage et renvoie du resultat en XML
$content_type = 'text/xml';
sdk_header($content_type);

echo <<<OUT_XML
	<data>
		<change>$change</change>
		<degree>$value</degree>
		<div>$divId</div>
		<fr>
			<short>$cardinal_fr_s</short>
			<long>$cardinal_fr_l</long>
		</fr>
		<en>
			<short>$cardinal_en_s</short>
			<long>$cardinal_en_l</long>
		</en>
	</data>
OUT_XML;
?>
