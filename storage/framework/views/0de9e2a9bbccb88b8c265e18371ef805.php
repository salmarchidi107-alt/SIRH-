<?php $__env->startSection('title', 'Actualités'); ?>
<?php $__env->startSection('page-title', 'Gestion des actualités'); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header">
        <div class="card-title">Liste des actualités</div>
        <a href="<?php echo e(route('news.create')); ?>" class="btn btn-primary">+ Nouvelle actualité</a>
    </div>
    <div class="card-body">

        <div class="main-layout">

            
            <div class="calendar-column">
                <div class="card holiday-calendar-card">
                    <div class="card-header">
                        <div class="card-title">Jours Fériés – Maroc</div>
                        <div class="calendar-nav">
                            <button class="btn btn-ghost btn-sm" id="prevMonth">&#8249;</button>
                            <span id="currentMonthLabel" style="font-weight:600;font-size:.95rem;min-width:140px;text-align:center"></span>
                            <button class="btn btn-ghost btn-sm" id="nextMonth">&#8250;</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="calendarLoader" class="calendar-loader">
                            <div class="spinner"></div>
                            <span>Chargement…</span>
                        </div>
                        <div id="calendarError" class="calendar-error" style="display:none">
                            ⚠️ Impossible de charger les jours fériés.
                        </div>
                        <div id="calendarWrap" style="display:none">
                            <div class="calendar-grid-header">
                                <div>Lun</div><div>Mar</div><div>Mer</div>
                                <div>Jeu</div><div>Ven</div><div>Sam</div><div>Dim</div>
                            </div>
                            <div class="calendar-grid" id="calendarGrid"></div>
                            <div id="holidayTooltip" class="holiday-tooltip" style="display:none"></div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="news-column">
                <?php if($news->isEmpty()): ?>
                    <div style="padding:48px 32px;text-align:center;color:var(--text-muted);background:white;border-radius:16px;border:1px dashed #e2e8f0;">
                        <div style="font-weight:600;margin-bottom:4px">Aucune actualité</div>
                        <div style="font-size:.875rem">Les actualités apparaîtront ici une fois créées.</div>
                    </div>
                <?php else: ?>
                    <div class="news-grid">
                        <?php $__currentLoopData = $news; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="news-card">
                            <?php if($item->image): ?>
                            <div class="news-image">
                                <img src="<?php echo e(asset($item->image)); ?>" alt="<?php echo e($item->title); ?>">
                            </div>
                            <?php endif; ?>
                            <div class="news-content">
                                <div class="news-type">
                                    <span class="badge bg-<?php echo e($item->type === 'holiday' ? 'success' : ($item->type === 'promotion' ? 'warning' : 'primary')); ?>">
                                        <?php echo e(\App\Models\News::TYPES[$item->type] ?? $item->type); ?>

                                    </span>
                                    <?php if(!$item->is_active): ?>
                                    <span class="badge bg-secondary">Inactif</span>
                                    <?php endif; ?>
                                </div>
                                <h3 class="news-title"><?php echo e($item->title); ?></h3>
                                <?php if($item->description): ?>
                                <p class="news-description"><?php echo e(Str::limit($item->description, 100)); ?></p>
                                <?php endif; ?>
                                <div class="news-date">📅 <?php echo e($item->event_date->format('d/m/Y')); ?></div>
                                <div class="news-actions">
                                    <a href="<?php echo e(route('news.show', $item)); ?>" class="btn btn-ghost btn-sm">Voir</a>
                                    <a href="<?php echo e(route('news.edit', $item)); ?>" class="btn btn-ghost btn-sm">Modifier</a>
                                    <form action="<?php echo e(route('news.destroy', $item)); ?>" method="POST" style="display:inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Êtes-vous sûr?')">Supprimer</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <div style="margin-top:16px"><?php echo e($news->links()); ?></div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script>
(function () {
    const PROXY_BASE = '<?php echo e(url("holidays")); ?>';
    let currentYear  = new Date().getFullYear();
    let currentMonth = new Date().getMonth() + 1;
    let holidaysCache = {};

    const monthNames = [
        'Janvier','Février','Mars','Avril','Mai','Juin',
        'Juillet','Août','Septembre','Octobre','Novembre','Décembre'
    ];

    const loader  = document.getElementById('calendarLoader');
    const errorEl = document.getElementById('calendarError');
    const wrap    = document.getElementById('calendarWrap');
    const grid    = document.getElementById('calendarGrid');
    const label   = document.getElementById('currentMonthLabel');
    const tooltip = document.getElementById('holidayTooltip');

    document.getElementById('prevMonth').addEventListener('click', () => {
        currentMonth--;
        if (currentMonth < 1) { currentMonth = 12; currentYear--; }
        renderMonth();
    });
    document.getElementById('nextMonth').addEventListener('click', () => {
        currentMonth++;
        if (currentMonth > 12) { currentMonth = 1; currentYear++; }
        renderMonth();
    });

    async function fetchHolidays(year, month) {
        const key = `${year}-${String(month).padStart(2,'0')}`;
        if (holidaysCache[key]) return holidaysCache[key];
        try {
            const res = await fetch(`${PROXY_BASE}/${year}/${month}`, {
                headers: { 'Accept':'application/json','X-Requested-With':'XMLHttpRequest' }
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const json = await res.json();
            const list = Array.isArray(json) ? json : (json.data ?? json.holidays ?? []);
            holidaysCache[key] = list;
            return list;
        } catch { return null; }
    }

    function getHolidayDates(holidays) {
        const map = {};
        holidays.forEach(h => { if (h.day) map[h.day] = h; });
        return map;
    }

    async function renderMonth() {
        label.textContent = `${monthNames[currentMonth-1]} ${currentYear}`;
        loader.style.display = 'flex';
        errorEl.style.display = 'none';
        wrap.style.display = 'none';
        grid.innerHTML = '';

        const holidays = await fetchHolidays(currentYear, currentMonth);
        loader.style.display = 'none';

        if (holidays === null) { errorEl.style.display = 'block'; return; }

        wrap.style.display = 'block';
        const holidayMap = getHolidayDates(holidays);

        const today = new Date();
        const todayDay = today.getDate(), todayMonth = today.getMonth()+1, todayYear = today.getFullYear();

        const firstDate = new Date(currentYear, currentMonth - 1, 1);
        let startDow = firstDate.getDay();
        startDow = (startDow === 0) ? 6 : startDow - 1;

        const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();

        for (let i = 0; i < startDow; i++) {
            const blank = document.createElement('div');
            blank.className = 'cal-day empty';
            grid.appendChild(blank);
        }

        for (let d = 1; d <= daysInMonth; d++) {
            const cell = document.createElement('div');
            cell.className = 'cal-day';
            cell.textContent = d;

            const dow = (startDow + d - 1) % 7;
            if (dow === 5 || dow === 6) cell.classList.add('weekend');
            if (d === todayDay && currentMonth === todayMonth && currentYear === todayYear)
                cell.classList.add('today');

            if (holidayMap[d]) {
                cell.classList.add('holiday');
                const dot = document.createElement('span');
                dot.className = 'holiday-dot';
                cell.appendChild(dot);
                const h = holidayMap[d];
                const name = h.description ?? h.name ?? 'Jour Férié';
                cell.addEventListener('mouseenter', (e) => {
                    tooltip.innerHTML = `<strong>${name}</strong>`;
                    tooltip.style.display = 'block';
                    positionTooltip(e);
                });
                cell.addEventListener('mousemove', positionTooltip);
                cell.addEventListener('mouseleave', () => tooltip.style.display = 'none');
            }
            grid.appendChild(cell);
        }
    }

    function positionTooltip(e) {
        tooltip.style.left = (e.clientX - tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top  = (e.clientY - tooltip.offsetHeight - 14) + 'px';
    }

    renderMonth();
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\SIRH-\resources\views/news/index.blade.php ENDPATH**/ ?>