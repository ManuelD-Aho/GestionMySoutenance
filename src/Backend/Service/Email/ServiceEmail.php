<?php

namespace App\Backend\Service\Email;

use App\Backend\Exception\EmailException;
use App\Backend\Exception\ModeleNonTrouveException;

class ServiceEmail implements ServiceEmailInterface
{
    private string $expediteurParDefaut;
    private string $nomExpediteurParDefaut;
    private string $cheminModelesEmail;

    public function __construct(
        string $expediteurParDefaut = 'noreply@gestionmysoutenance.ci',
        string $nomExpediteurParDefaut = 'Gestion MySoutenance',
        string $cheminModelesEmail = __DIR__ . '/../../../../templates/emails/'
    ) {
        $this->expediteurParDefaut = $expediteurParDefaut;
        $this->nomExpediteurParDefaut = $nomExpediteurParDefaut;
        $this->cheminModelesEmail = rtrim($cheminModelesEmail, '/') . '/';
    }

    public function envoyerEmail(
        string $destinataire,
        string $sujet,
        string $corpsMessage,
        array $entetes = [],
        bool $estHtml = false
    ): bool {
        $sujetEncode = mb_encode_mimeheader($sujet, 'UTF-8', 'B');

        $entetesInternes = [];
        $entetesInternes['From'] = mb_encode_mimeheader($this->nomExpediteurParDefaut, 'UTF-8', 'B') . ' <' . $this->expediteurParDefaut . '>';
        $entetesInternes['Reply-To'] = $this->expediteurParDefaut;
        $entetesInternes['MIME-Version'] = '1.0';
        $entetesInternes['Content-Transfer-Encoding'] = '8bit';

        if ($estHtml) {
            $entetesInternes['Content-Type'] = 'text/html; charset=UTF-8';
        } else {
            $entetesInternes['Content-Type'] = 'text/plain; charset=UTF-8';
        }

        $entetesFinales = array_merge($entetesInternes, $entetes);
        $chaineEntetes = '';
        foreach ($entetesFinales as $cle => $valeur) {
            $chaineEntetes .= $cle . ': ' . $valeur . "\r\n";
        }

        $corpsMessageFinal = wordwrap($corpsMessage, 70, "\r\n");

        if (!mail($destinataire, $sujetEncode, $corpsMessageFinal, $chaineEntetes)) {
            throw new EmailException("La fonction mail() n'a pas pu envoyer l'email à '$destinataire' avec le sujet '$sujet'.");
        }
        return true;
    }

    public function envoyerEmailAvecModele(
        string $destinataire,
        string $sujet,
        string $nomModele,
        array $donneesModele = [],
        array $entetes = []
    ): bool {
        $cheminFichierModele = $this->cheminModelesEmail . $nomModele . '.phtml';
        if (!file_exists($cheminFichierModele) || !is_readable($cheminFichierModele)) {
            throw new ModeleNonTrouveException("Le modèle d'email '$nomModele' est introuvable ou illisible à l'emplacement : $cheminFichierModele");
        }

        extract($donneesModele);
        ob_start();
        include $cheminFichierModele;
        $corpsMessage = ob_get_clean();

        $estHtml = true;
        if (strpos(strtolower($nomModele), '.txt') !== false || strpos(strtolower($nomModele), '_text') !== false) {
            $estHtml = false;
        }

        return $this->envoyerEmail($destinataire, $sujet, $corpsMessage, $entetes, $estHtml);
    }
}