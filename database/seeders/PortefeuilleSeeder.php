<?php

namespace Database\Seeders;

use App\Models\Assure;
use App\Models\Contrat;
use App\Models\Garantie;
use App\Models\Piece;
use App\Models\Sinistre;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

/**
 * Portefeuille de demonstration : assures, contrats, garanties, sinistres.
 *
 * Le jeu est **reproductible** : graine fixe et dates ancrees sur une date
 * de reference absolue, jamais sur "aujourd'hui". C'est ce qui permet aux
 * tickets du backlog de citer des chiffres et des dates qui tombent juste
 * chez tout le monde, aujourd'hui comme dans six mois.
 */
class PortefeuilleSeeder extends Seeder
{
    /** Date de reference du jeu de donnees. */
    public const ANCRE = '2026-08-31';

    private const GRAINE = 20260831;

    private const NB_ASSURES = 40;

    private const NB_SINISTRES = 130;

    /** Garanties portees par chaque produit. */
    private const GARANTIES_PAR_PRODUIT = [
        'auto' => ['COL', 'BDG', 'VOL', 'RC'],
        'moto' => ['COL', 'BDG', 'VOL', 'RC'],
        'habitation' => ['DDE', 'INC', 'VOL', 'RC'],
        'rc_pro' => ['RC'],
    ];

    /** Natures de sinistre plausibles pour chaque produit. */
    private const NATURES_PAR_PRODUIT = [
        'auto' => ['collision', 'bris_de_glace', 'vol', 'rc'],
        'moto' => ['collision', 'bris_de_glace', 'vol', 'rc'],
        'habitation' => ['degat_des_eaux', 'incendie', 'vol', 'rc'],
        'rc_pro' => ['rc'],
    ];

    private const PLAFONDS = ['COL' => 2500000, 'BDG' => 150000, 'VOL' => 1200000, 'RC' => 5000000, 'DDE' => 1000000, 'INC' => 5000000];

    private const FRANCHISES = ['COL' => 25000, 'BDG' => 7500, 'VOL' => 20000, 'RC' => 0, 'DDE' => 15000, 'INC' => 20000];

    private const GESTIONNAIRES = ['C. Meunier', 'A. Rossi', 'K. Diallo', 'L. Fabre'];

    public function run(): void
    {
        $fake = fake('fr_FR');
        $fake->seed(self::GRAINE);
        mt_srand(self::GRAINE);

        $ancre = CarbonImmutable::parse(self::ANCRE);

        $assures = $this->assures($fake);
        $contrats = $this->contrats($fake, $assures, $ancre);
        $this->sinistres($fake, $contrats, $ancre);
    }

    /**
     * @return array<int, Assure>
     */
    private function assures($fake): array
    {
        $assures = [];

        for ($i = 1; $i <= self::NB_ASSURES; $i++) {
            $prenom = $fake->firstName();
            $nom = $fake->lastName();

            $assures[] = Assure::create([
                'reference' => sprintf('ASS-2026-%03d', $i),
                'civilite' => $this->pick(['M.', 'Mme']),
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $this->email($prenom, $nom, $i),
                'telephone' => '0'.mt_rand(600000000, 699999999),
                'date_naissance' => sprintf('%d-%02d-%02d', mt_rand(1956, 2004), mt_rand(1, 12), mt_rand(1, 28)),
                'adresse' => mt_rand(1, 120).' '.$fake->streetName(),
                'code_postal' => sprintf('%05d', mt_rand(1000, 95999)),
                'ville' => $fake->city(),
            ]);
        }

        return $assures;
    }

    /**
     * @param  array<int, Assure>  $assures
     * @return array<int, Contrat>
     */
    private function contrats($fake, array $assures, CarbonImmutable $ancre): array
    {
        $contrats = [];
        $n = 0;

        foreach ($assures as $assure) {
            foreach (range(1, mt_rand(1, 3)) as $ignored) {
                $produit = $this->pick(Contrat::PRODUITS);
                $effet = $ancre->subMonths(mt_rand(8, 72))->startOfMonth();

                $contrat = Contrat::create([
                    'assure_id' => $assure->id,
                    'reference' => sprintf('CTR-2026-%04d', ++$n),
                    'produit' => $produit,
                    'formule' => $this->pick(Contrat::FORMULES),
                    'date_effet' => $effet->toDateString(),
                    'date_echeance' => $effet->addYear()->subDay()->toDateString(),
                    'statut' => $this->pondere(['actif' => 78, 'suspendu' => 8, 'resilie' => 14]),
                    'prime_annuelle_cents' => mt_rand(180, 2400) * 100,
                    'franchise_cents' => $this->pick([10000, 15000, 20000, 30000]),
                ]);

                foreach (self::GARANTIES_PAR_PRODUIT[$produit] as $code) {
                    Garantie::create([
                        'contrat_id' => $contrat->id,
                        'code' => $code,
                        'libelle' => Garantie::LIBELLES[$code],
                        'plafond_cents' => self::PLAFONDS[$code],
                        'franchise_cents' => self::FRANCHISES[$code],
                        'incluse' => true,
                    ]);
                }

                $contrats[] = $contrat;
            }
        }

        return $contrats;
    }

    /**
     * @param  array<int, Contrat>  $contrats
     */
    private function sinistres($fake, array $contrats, CarbonImmutable $ancre): void
    {
        $eligibles = array_values(array_filter($contrats, fn (Contrat $c) => $c->statut !== 'resilie'));

        for ($i = 1; $i <= self::NB_SINISTRES; $i++) {
            $contrat = $eligibles[mt_rand(0, count($eligibles) - 1)];

            // Les quatre derniers dossiers sont declares le jour de l'ancre,
            // a des heures ouvrees : ils servent de repere pour les filtres.
            $surLAncre = $i > self::NB_SINISTRES - 4;

            $survenu = $surLAncre
                ? $ancre->subDays(mt_rand(1, 4))
                : $ancre->subDays(mt_rand(10, 540));

            $declare = $surLAncre
                ? $ancre->setTime(self::pickIn([9, 11, 14, 16]), self::pickIn([5, 12, 30, 45]))
                : $survenu->addDays(mt_rand(0, 9))->setTime(mt_rand(8, 18), mt_rand(0, 59));

            $statut = $surLAncre ? 'declare' : $this->pondere([
                'declare' => 14, 'en_cours' => 26, 'expertise' => 15, 'clos' => 36, 'refuse' => 9,
            ]);

            $sinistre = Sinistre::create([
                'contrat_id' => $contrat->id,
                'reference' => sprintf('SIN-2026-%05d', $i),
                'nature' => $this->pick(self::NATURES_PAR_PRODUIT[$contrat->produit]),
                'survenu_le' => $survenu->toDateString(),
                'declare_le' => $declare->toDateTimeString(),
                'description' => $fake->sentence(mt_rand(8, 18)),
                'statut' => $statut,
                'montant_estime_cents' => mt_rand(35000, 1800000),
                'montant_regle_cents' => null,
                'gestionnaire' => $this->pick(self::GESTIONNAIRES),
            ]);

            if ($statut === 'clos') {
                $sinistre->update(['montant_regle_cents' => $sinistre->indemniteCents()]);
            }

            foreach (range(0, mt_rand(0, 3)) as $k) {
                if ($k === 0 && mt_rand(0, 3) === 0) {
                    continue;
                }

                $type = $this->pick(Piece::TYPES);

                Piece::create([
                    'sinistre_id' => $sinistre->id,
                    'libelle' => Piece::LIBELLES[$type],
                    'type' => $type,
                    'recue_le' => $declare->addDays(mt_rand(0, 12))->toDateString(),
                ]);
            }
        }
    }

    /**
     * @template T
     *
     * @param  array<int, T>  $choix
     * @return T
     */
    private function pick(array $choix)
    {
        return $choix[mt_rand(0, count($choix) - 1)];
    }

    private static function pickIn(array $choix): int
    {
        return $choix[mt_rand(0, count($choix) - 1)];
    }

    /**
     * Tirage pondere : ['actif' => 78, 'resilie' => 22].
     *
     * @param  array<string, int>  $poids
     */
    private function pondere(array $poids): string
    {
        $tirage = mt_rand(1, array_sum($poids));

        foreach ($poids as $valeur => $poid) {
            $tirage -= $poid;

            if ($tirage <= 0) {
                return $valeur;
            }
        }

        return array_key_first($poids);
    }

    private function email(string $prenom, string $nom, int $i): string
    {
        $slug = fn (string $s) => preg_replace('/[^a-z]/', '', strtolower(
            iconv('UTF-8', 'ASCII//TRANSLIT', $s)
        ));

        return sprintf('%s.%s%d@exemple.test', $slug($prenom), $slug($nom), $i);
    }
}
