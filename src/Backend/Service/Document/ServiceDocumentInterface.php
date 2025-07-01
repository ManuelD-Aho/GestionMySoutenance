<?php

namespace App\Backend\Service\Document;

interface ServiceDocumentInterface
{
    /**
     * Génère un document PDF personnalisé.
     * @param string $template Le template à utiliser.
     * @param array $donnees Les données pour le document.
     * @param array $options Les options de génération.
     * @return string Le chemin du document généré.
     */
    public function genererDocumentPDF(string $template, array $donnees, array $options = []): string;

    /**
     * Crée un template de document.
     * @param string $nomTemplate Le nom du template.
     * @param string $contenuTemplate Le contenu du template.
     * @param array $variables Les variables disponibles.
     * @return string L'ID du template créé.
     */
    public function creerTemplate(string $nomTemplate, string $contenuTemplate, array $variables): string;

    /**
     * Modifie un template existant.
     * @param string $idTemplate L'ID du template.
     * @param array $donneesModification Les données à modifier.
     * @return bool Vrai si la modification a réussi.
     */
    public function modifierTemplate(string $idTemplate, array $donneesModification): bool;

    /**
     * Supprime un template.
     * @param string $idTemplate L'ID du template.
     * @return bool Vrai si la suppression a réussi.
     */
    public function supprimerTemplate(string $idTemplate): bool;

    /**
     * Liste tous les templates disponibles.
     * @param array $filtres Les critères de filtrage.
     * @return array La liste des templates.
     */
    public function listerTemplates(array $filtres = []): array;

    /**
     * Fusionne plusieurs documents PDF.
     * @param array $cheminsFichiers Les chemins des fichiers à fusionner.
     * @param string $nomFichierSortie Le nom du fichier de sortie.
     * @return string Le chemin du document fusionné.
     */
    public function fusionnerDocumentsPDF(array $cheminsFichiers, string $nomFichierSortie): string;

    /**
     * Convertit un document vers un autre format.
     * @param string $cheminSource Le chemin du fichier source.
     * @param string $formatCible Le format de destination.
     * @param array $options Les options de conversion.
     * @return string Le chemin du document converti.
     */
    public function convertirDocument(string $cheminSource, string $formatCible, array $options = []): string;

    /**
     * Ajoute un filigrane à un document PDF.
     * @param string $cheminDocument Le chemin du document.
     * @param string $texteFiligrane Le texte du filigrane.
     * @param array $parametres Les paramètres du filigrane.
     * @return string Le chemin du document avec filigrane.
     */
    public function ajouterFiligrane(string $cheminDocument, string $texteFiligrane, array $parametres = []): string;

    /**
     * Signe numériquement un document.
     * @param string $cheminDocument Le chemin du document.
     * @param string $certificat Le certificat de signature.
     * @param string $motDePasse Le mot de passe du certificat.
     * @return string Le chemin du document signé.
     */
    public function signerDocument(string $cheminDocument, string $certificat, string $motDePasse): string;

    /**
     * Archive des documents.
     * @param array $documentsIds Les IDs des documents à archiver.
     * @param string $motifArchivage Le motif d'archivage.
     * @return bool Vrai si l'archivage a réussi.
     */
    public function archiverDocuments(array $documentsIds, string $motifArchivage): bool;

    /**
     * Recherche des documents selon des critères.
     * @param array $criteres Les critères de recherche.
     * @param int $page Le numéro de page.
     * @param int $elementsParPage Le nombre d'éléments par page.
     * @return array Les résultats de la recherche.
     */
    public function rechercherDocuments(array $criteres, int $page = 1, int $elementsParPage = 20): array;

    /**
     * Valide l'intégrité d'un document.
     * @param string $cheminDocument Le chemin du document.
     * @return array Le résultat de la validation.
     */
    public function validerIntegriteDocument(string $cheminDocument): array;
}