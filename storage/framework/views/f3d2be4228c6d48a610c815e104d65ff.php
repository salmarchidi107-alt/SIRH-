
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 11pt;
    color: #000;
    background: #fff;
    padding: 22mm 28mm 22mm 28mm;
    line-height: 1.5;
  }
  .title {
    text-align: center;
    font-weight: bold;
    font-size: 12pt;
    margin-top: 12mm;
    margin-bottom: 10mm;
  }
  .field    { margin-bottom: 7mm; font-size: 11pt; }
  .date-right { text-align: right; margin: 10px; }
  hr { border: none; border-top: 0.5px solid #000; margin-bottom: 6mm; }
  .note { font-size: 10pt; text-align: justify; line-height: 1.5; margin-bottom: 4mm; }
  .sig-line { display: inline-block; width: 160px; border-bottom: 1px solid #000; }
</style>
</head>
<body>

<div class="field">Nom de société</div>
<div class="field">Numéro de demande : ABS-<?php echo e($absence->start_date->year); ?>-<?php echo e(str_pad($absence->id, 5, '0', STR_PAD_LEFT)); ?></div>

<div class="title">DEMANDE D'ABSENCE</div>

<div class="field">Nom de l'employé : <?php echo e($absence->employee->full_name); ?></div>
<div class="field">Type d'absence : <?php echo e(\App\Models\Absence::TYPES[$absence->type] ?? $absence->type); ?></div>
<div class="field">Date de début : <?php echo e($absence->start_date->format('d/m/Y')); ?></div>
<div class="field">Date de fin : <?php echo e($absence->end_date->format('d/m/Y')); ?></div>
Motif :<br><?php echo e($absence->reason ?: 'Non spécifié'); ?>

<div class="field">Employé remplaçant : <?php echo e($absence->replacement?->full_name ?? ''); ?></div>

<div style="margin-bottom: 7mm;">
  Signature de l'employé : <span class="sig-line">&nbsp;</span>
</div>
<div class="date-right">Date :</div>

<hr>

<p class="note">
  <strong>NB</strong> : L'employeur se réserve le droit de refuser ou de reporter toute demande d'absence
  pour des raisons liées à l'organisation du service ou à la continuité des activités.
</p>
<p class="note">
  Pour certains types d'absence, la fourniture d'un justificatif est obligatoire. À défaut,
  l'absence pourra être requalifiée en absence injustifiée.
</p>
<p class="note">
  Le collaborateur demeure responsable de l'organisation de la continuité de ses activités. La
  désignation d'un remplaçant doit être validée par la hiérarchie afin de garantir le bon
  fonctionnement du service.
</p>

</body>
</html><?php /**PATH C:\Users\HP\SIRH-\resources\views/absences/pdf.blade.php ENDPATH**/ ?>