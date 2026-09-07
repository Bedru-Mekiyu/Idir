# 1. chore(docs): remove outdated verification screenshots
git add screenshots/
git commit -m "chore(docs): remove outdated verification screenshots"

# 2. docs(architecture): add comprehensive architecture documents and design decisions
git add CONFIG.md DECISIONS.md DESIGN_ITERATIONS.md VERIFICATION_LOG.md README.md
git commit -m "docs(architecture): add comprehensive architecture documents and design decisions"

# 3. feat(api): implement structured payment gateway integration with Chapa and Telebirr
git add app/Services/Payments/ app/Jobs/ProcessPaymentWebhookJob.php tests/Feature/PaymentGatewayTest.php tests/Feature/ExpirePendingPaymentsTest.php
git commit -m "feat(api): implement structured payment gateway integration with Chapa and Telebirr"

# 4. feat(auth): implement idir creation access request workflow with admin approval
git add app/Models/AccessRequest.php app/Http/Controllers/AccessRequestController.php database/migrations/2026_09_04_160151_create_access_requests_table_and_add_can_create_idir_to_users_table.php app/Filament/Admin/Resources/AccessRequestResource.php app/Filament/Admin/Resources/AccessRequestResource/ tests/Feature/IdirApprovalWorkflowTest.php tests/Feature/TenantRegistrationTest.php
git commit -m "feat(auth): implement idir creation access request workflow with admin approval"

# 5. feat(ui): add unified member profile management and avatar uploads
git add app/Http/Controllers/MemberProfileController.php database/migrations/2026_09_07_162912_add_profile_photo_path_to_users_table.php resources/views/member/profile.blade.php tests/Feature/MemberPortalTest.php tests/Feature/MemberAuthTest.php
git commit -m "feat(ui): add unified member profile management and avatar uploads"

# 6. feat(auth): add dedicated committee login page and restrict multiple sessions
git add app/Filament/Pages/CommitteeLogin.php app/Http/Middleware/OneSessionPerUser.php
git commit -m "feat(auth): add dedicated committee login page and restrict multiple sessions"

# 7. test(e2e): add manager access authorization and isolated end-to-end tests
git add tests/e2e/live-manager-access-authorization.js tests/Feature/ManagerAccessAuthorizationTest.php
git commit -m "test(e2e): add manager access authorization and isolated end-to-end tests"

# 8. refactor(ui): enforce strict 3-color minimalist design across blade components
git add resources/views/ public/favicon.svg
git commit -m "refactor(ui): enforce strict 3-color minimalist design across blade components"

# 9. test(core): improve test coverage for ledger, approvals, and webhook security
git add tests/
git commit -m "test(core): improve test coverage for ledger, approvals, and webhook security"

# 10. chore(config): add routing and AI agent tracking configuration
git add routes/ .agents/ .claude/ .junie/
git commit -m "chore(config): add routing and AI agent tracking configuration"

# Add anything else remaining (safety net)
git add .
git commit -m "chore: finalize remaining untracked components and layout tweaks"

git log --oneline -n 11
git status
