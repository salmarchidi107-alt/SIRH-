<?php $__env->startSection('title', 'Actualités'); ?>
<?php $__env->startSection('page-title', 'Gestion des actualités'); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header">
        <div class="card-title">📰 Liste des actualités</div>
        <a href="<?php echo e(route('news.create')); ?>" class="btn btn-primary">+ Nouvelle actualité</a>
    </div>
    <div class="card-body">
        <?php if($news->isEmpty()): ?>
            <div style="padding:32px;text-align:center;color:var(--text-muted)">
                <div style="font-size:2rem;margin-bottom:8px">📰</div>
                <div>Aucune actualité</div>
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
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr?')">Supprimer</button>
                            </form>
                        </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php echo e($news->links()); ?>

        <?php endif; ?>
    </div>


<div class="card holiday-calendar-card" style="margin-top:24px">
    <div class="card-header">
        <div class="card-title"> Jours Fériés - Maroc</div>
        <div class="calendar-nav">
            <button class="btn btn-ghost btn-sm" id="prevMonth">&#8249;</button>
            <span id="currentMonthLabel" style="font-weight:600;font-size:1rem;min-width:160px;text-align:center"></span>
            <button class="btn btn-ghost btn-sm" id="nextMonth">&#8250;</button>
        </div>
    </div>
    <div class="card-body">
        <div id="calendarLoader" class="calendar-loader">
            <div class="spinner"></div>
            <span>Chargement des jours fériés...</span>
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

<style>
/* ========== NEWS ========== */
.news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 24px;
    padding: 16px 0;
}
.news-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -1px rgba(0,0,0,.06);
    transition: transform .2s, box-shadow .2s;
    border: 1px solid #e2e8f0;
}
.news-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 15px -3px rgba(0,0,0,.1), 0 4px 6px -2px rgba(0,0,0,.05);
}
.news-image { width:100%; height:180px; overflow:hidden; background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); }
.news-image img { width:100%; height:100%; object-fit:cover; }
.news-content { padding:20px; }
.news-type { display:flex; gap:8px; margin-bottom:12px; }
.news-title { font-size:1.25rem; font-weight:600; margin:0 0 8px; color:#1e293b; line-height:1.4; }
.news-description { color:#64748b; font-size:.875rem; margin:0 0 12px; line-height:1.5; }
.news-date { color:#94a3b8; font-size:.875rem; margin-bottom:16px; }
.news-actions { display:flex; gap:8px; flex-wrap:wrap; }

/* ========== CALENDAR ========== */
.holiday-calendar-card .card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}







.holiday-calendar-card .card-body {
    padding: 12px;
}

.cal-day {
    min-height: 24px !important;
    font-size: .65rem !important;
    padding: 2px !important;
}

.cal-day {
    min-height: 32px;
    font-size: .75rem;
}
.calendar-nav {
    display: flex;
    align-items: center;
    gap: 8px;
}
.calendar-grid-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    text-align: center;
    font-weight: 600;
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #94a3b8;
    margin-bottom: 8px;
    padding: 0 4px;
}
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
}
.cal-day {
    position: relative;
    aspect-ratio: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    font-size: .9rem;
    font-weight: 500;
    cursor: default;
    transition: background .15s, transform .15s;
    border: 2px solid transparent;
    min-height: 44px;
}
.cal-day.empty {
    background: transparent;
}
.cal-day.today {
    background: #eff6ff;
    border-color: #3b82f6;
    color: #1d4ed8;
    font-weight: 700;
}
.cal-day.weekend {
    color: #94a3b8;
}
.cal-day.holiday {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-color: #f59e0b;
    color: #92400e;
    cursor: pointer;
    font-weight: 700;
}
.cal-day.holiday:hover {
    transform: scale(1.08);
    box-shadow: 0 4px 12px rgba(245,158,11,.3);
    z-index: 2;
}
.cal-day.holiday .holiday-dot {
    width: 5px;
    height: 5px;
    background: #f59e0b;
    border-radius: 50%;
    position: absolute;
    bottom: 5px;
}
.cal-day.holiday.today {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-color: #f59e0b;
    color: #92400e;
}
.holiday-tooltip {
    position: fixed;
    background: #1e293b;
    color: white;
    padding: 10px 14px;
    border-radius: 10px;
    font-size: .8rem;
    max-width: 220px;
    z-index: 9999;
    pointer-events: none;
    box-shadow: 0 8px 24px rgba(0,0,0,.2);
    line-height: 1.5;
}
.holiday-tooltip::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 6px solid transparent;
    border-top-color: #1e293b;
}

/* Loading */
.calendar-loader {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 32px;
    color: #64748b;
    justify-content: center;
}
.spinner {
    width: 22px; height: 22px;
    border: 3px solid #e2e8f0;
    border-top-color: #3b82f6;
    border-radius: 50%;
    animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
.calendar-error {
    padding: 24px;
    text-align: center;
    color: #ef4444;
    background: #fef2f2;
    border-radius: 12px;
    font-size: .9rem;
}
</style>

<script>
(function () {
    // Calls your Laravel proxy route: GET /index.php/holidays/{year}/{month}
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
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const json = await res.json();
            // Normalize: accept array OR { data: [] } OR { holidays: [] }
            const list = Array.isArray(json) ? json : (json.data ?? json.holidays ?? []);
            holidaysCache[key] = list;
            return list;
        } catch {
            return null;
        }
    }

    function getHolidayDates(holidays) {
        const map = {};
        holidays.forEach(h => {
            // API returns { day: 1, month: 1, date: "2025-01-01", description: "..." }
            if (h.day) map[h.day] = h;
        });
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
                    tooltip.innerHTML = ` <strong>${name}</strong>`;
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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Projects\HospitalRh\resources\views/news/index.blade.php ENDPATH**/ ?>