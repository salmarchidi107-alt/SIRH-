<?php

namespace App\DTOs;

class CompteurMoisDTO
{
    public readonly float $heures_planifiees;
    public readonly float $heures_realisees;
    public readonly float $heures_supplementaires;
    public readonly float $ecart;
    public readonly float $solde_compteur;
    public readonly float $taux_realisation;

    public function __construct(
        float $heures_planifiees,
        float $heures_realisees,
        float $heures_supplementaires,
        ?float $ecart = null,
        ?float $solde_compteur = null,
        ?float $taux_realisation = null
    ) {
        $this->heures_planifiees = $heures_planifiees;
        $this->heures_realisees = $heures_realisees;
        $this->heures_supplementaires = $heures_supplementaires;
        $this->ecart = $ecart ?? ($heures_realisees + $heures_supplementaires - $heures_planifiees);
        $this->solde_compteur = $solde_compteur ?? $this->ecart;
        $this->taux_realisation = $taux_realisation ?? ($heures_planifiees > 0 ? round(($heures_realisees / $heures_planifiees) * 100, 1) : 0);
    }

    public static function defaults(): self
    {
        return new self(0, 0, 0);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['heures_planifiees'] ?? 0,
            $data['heures_realisees'] ?? 0,
            $data['heures_supplementaires'] ?? 0,
            $data['ecart'] ?? null,
            $data['solde_compteur'] ?? null,
            $data['taux_realisation'] ?? null
        );
    }

    public static function fromModel(object $model): self
    {
        return self::fromArray($model->toArray());
    }

    public function toArray(): array
    {
        return [
            'heures_planifiees' => $this->heures_planifiees,
            'heures_realisees' => $this->heures_realisees,
            'heures_supplementaires' => $this->heures_supplementaires,
            'ecart' => $this->ecart,
            'solde_compteur' => $this->solde_compteur,
            'taux_realisation' => $this->taux_realisation,
        ];
    }
}

