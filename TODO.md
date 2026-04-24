# Fix RouteNotFoundException: planning.monthly.pdf

## Steps
- [x] Understand the issue: `route('planning.monthly.pdf')` is used in monthly.blade.php but not defined.
- [x] Add route `GET /planning/monthly/pdf` -> `PlanningController::exportMonthlyPdf` in `routes/web.php`
- [x] Add `exportMonthlyPdf()` method in `PlanningController.php`
- [x] Clear route cache
- [x] Verified route exists: `GET planning/monthly/pdf` -> `PlanningController@exportMonthlyPdf`

