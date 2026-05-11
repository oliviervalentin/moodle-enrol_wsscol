<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'enrol_wsscol', language 'fr'.
 *
 * @package    enrol_wsscol
 */

$string['pluginname'] = 'WSscol - Inscriptions par webservices';
$string['pluginname_desc'] = 'La méthode d\inscription WSscol permet à un enseignant d\'inscrire tous les étudiants d\'un groupe pédagogique via un webservice';
$string['searchlabel'] = 'Code';
$string['searchlabel_desc'] = 'Code à chercher';
$string['add_instances_title'] = 'Ajouter plusieurs instances d\'inscriptions';
$string['webservices_title'] = 'Webservices';
$string['webservices_desc'] = 'Paramètres des webservices';
$string['syncenrolmentstask'] = 'Tâche de synchronisation des inscriptions wsscol';
$string['defaultrole'] = 'Rôle par défaut';
$string['defaultrole_desc'] = 'Sélectionnez le rôle qui doit être attribué aux utilisateurs lors de l\'inscription via wsscol';
$string['role'] = 'Rôle attribué par défaut';

// Add instance.
$string['zeroresult'] = 'Aucun résultat';
$string['searchstring'] = 'Code';
$string['searchplaceholder'] = 'Saisiez votre code';
$string['label'] = 'Choisissez une application de formation : ';
$string['code'] = 'Code';

// Lib.
$string['customint2'] = 'Application de formation';
$string['error_customint2'] = 'Aucun webservice configuré';
$string['bulk_enrolment_button'] = 'Inscription en masse';

// Wsmanage.
$string['wsmanage_link'] = 'Ajouter / gérer les webservices WSscol';

// Wsedit.
$string['wsedit_submitlabel'] = 'Ajouter un webservice';
$string['wsmanage_title'] = 'Gérer les webservices';
$string['wsedit_title'] = 'Ajouter un webservice';
$string['confirm_delete_ws'] = 'Êtes-vous sûr de vouloir supprimer ce webservice ?';
$string['add_ws_button'] = 'Ajouter un nouveau webservice';
$string[''] = '';
$string['type'] = 'Type d\'application scolaire';
$string['type_help'] = 'Choisir le type d\'application scolaire. Cette information peut être utilisée pour adapter les champs à remplir ou les traitements selon le type d\'application ciblé.';
$string['typegeneric'] = 'Générique';

$string['wsscol:manage'] = 'Gérer les inscriptions WSscol des utilisateurs.';
$string['wsscol:config'] = 'Ajouter ou modifier une instance d\'inscription WSscol dans un cours.';

$string['ws_section_general'] = 'Paramètres généraux';
$string['ws_section_connection'] = 'Connexion au webservice';
$string['ws_section_tokenauth'] = 'Authentification par token (optionnel)';
$string['ws_section_students'] = 'Récupération des étudiants';
$string['ws_section_mapping']  = 'Correspondance des identifiants';
$string['ws_section_groupsearch'] = 'Recherche de groupes (optionnel)';

$string['ws_name'] = 'Nom du webservice';
$string['ws_name_help'] = 'Indiquer un nom court pour identifier ce webservice (nom, dates...).';
$string['ws_role'] = 'Rôle attribué';
$string['ws_role_help'] = 'Rôle Moodle attribué aux étudiants inscrits via ce webservice.';
$string['ws_host'] = 'URL du serveur';
$string['ws_host_help'] = 'URL de base du serveur hébergeant le webservice.';
$string['ws_baseurl'] = 'Chemin de base de l\'API';
$string['ws_baseurl_help'] = 'Chemin à ajouter après l\'URL du serveur (exemple : api/ext/v1). Laisser vide si l\'URL complète est dans les URI.';
$string['ws_user'] = 'Identifiant';
$string['ws_password'] = 'Mot de passe';
$string['ws_tokenmethod'] = 'Méthode d\'authentification';
$string['ws_tokenmethod_help'] = 'Choisir le type d\'authentification. Laisser "Aucune" pour une auth HTTP basique.';
$string['ws_tokenmethod_none'] = 'Aucune (auth basique)';
$string['ws_authurl'] = 'URL d\'authentification token';
$string['ws_authurl_help'] = 'URL de l\'endpoint d\'authentification pour obtenir un token (exemple : URL CAS pour PEGASE).';
$string['ws_tokenauth_desc'] = 'Cette section est optionnelle. Ne la remplir que si le webservice utilise une authentification par token plutôt qu\'une authentification HTTP basique.';
$string['ws_getstudents_uri'] = 'URI de récupération des étudiants';
$string['ws_getstudents_uri_help'] = 'URI appelée pour récupérer la liste des étudiants. Utiliser [search] pour le code du groupe, [periode] pour la période, [structure] pour le code établissement. Exemple pour PEGASE : objet-formation/[structure]/[periode]/[search]/cursus';
$string['ws_getstudents_uri_desc'] ='L\'URI supporte les placeholders suivants : <strong>[search]</strong> (code du groupe saisi par l\'enseignant), <strong>[periode]</strong> (période académique), <strong>[structure]</strong> (code établissement).';
$string['ws_getstudents_periode'] = 'Valeur de la période [periode]';
$string['ws_getstudents_periode_help'] = 'Valeur à substituer au placeholder [periode] dans l\'URI sous forme du code court (format : PERIODE-XX-XX).';
$string['ws_getstudents_structure'] = 'Valeur du code établissement [structure]';
$string['ws_getstudents_structure_help'] = 'Valeur à substituer au placeholder [structure] dans l\'URI.';
$string['ws_getstudent_id_ws'] = 'Champ identifiant dans la réponse WS';
$string['ws_getstudent_id_ws_help'] = 'Nom du champ retourné par le webservice contenant l\'identifiant de l\'étudiant.';
$string['ws_getstudent_id_local'] = 'Champ correspondant dans Moodle';
$string['ws_getstudent_id_local_help'] = 'Champ du profil utilisateur Moodle à utiliser pour faire correspondre l\'identifiant retourné par le webservice.';
$string['ws_mapping_desc'] = 'Ces champs vont définir la correspondance entre l\'identifiant retourné par le webservice et le champ du profil utilisateur Moodle.';
$string['ws_searchgroups_uri'] = 'URI de recherche de groupes';
$string['ws_searchgroups_code_ws'] = 'Champ code groupe dans la réponse WS';
$string['ws_searchgroups_name_ws'] = 'Champ nom groupe dans la réponse WS';
$string['ws_groupsearch_desc'] = 'Cette section est optionnelle. Ne la remplir que si le webservice propose un endpoint de recherche de groupes.';
$string['wsedit_title_add'] = 'Ajouter un webservice';
$string['wsedit_title_edit'] = 'Modifier un webservice';