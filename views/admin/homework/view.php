<?php
// File: /views/admin/homework/view.php
$pageTitle = 'Homework Details | ROGELE';
require_once __DIR__ . '/../../layouts/admin_header.php';

$homework    = $homework ?? [];
$submissions = $submissions ?? [];
?>

<div class="admin-view-container">

    <div class="view-header-card">
        <div class="header-main">
            <div class="title-area">
                <span class="category-badge">
                    <i class="fas fa-book" aria-hidden="true"></i> <?php echo htmlspecialchars($homework['subject_name'] ?? 'General Subject'); ?>
                </span>
                <h1 class="page-title"><?php echo htmlspecialchars($homework['title'] ?? 'Homework Details'); ?></h1>
                <p class="teacher-info">
                    <i class="fas fa-chalkboard-teacher" aria-hidden="true"></i> Assigned by <strong><?php echo htmlspecialchars($homework['teacher_name'] ?? 'Unknown Teacher'); ?></strong>
                </p>
            </div>
            
            <div class="header-actions">
                <?php 
                    $dueDate   = !empty($homework['due_date']) ? strtotime($homework['due_date']) : 0;
                    $isExpired = $dueDate > 0 && $dueDate < time();
                    $isActive  = !empty($homework['is_active']);
                ?>
                <?php if (!$isActive): ?>
                    <span class="status-pill status-disabled"><i class="fas fa-ban" aria-hidden="true"></i> Disabled</span>
                <?php elseif ($isExpired): ?>
                    <span class="status-pill status-expired"><i class="fas fa-clock" aria-hidden="true"></i> Expired</span>
                <?php else: ?>
                    <span class="status-pill status-active"><i class="fas fa-check-circle" aria-hidden="true"></i> Active</span>
                <?php endif; ?>

                <a href="<?php echo BASE_URL; ?>/admin/homework/toggle-status/<?php echo urlencode($homework['id'] ?? ''); ?>" 
                   class="btn-action-outline" 
                   onclick="return confirm('Toggle status for this assignment?')">
                    <i class="fas <?php echo $isActive ? 'fa-eye-slash' : 'fa-eye'; ?>" aria-hidden="true"></i>
                    <?php echo $isActive ? 'Disable' : 'Enable'; ?>
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/homework/delete/<?php echo urlencode($homework['id'] ?? ''); ?>" 
                   class="btn-action-danger" 
                   onclick="return confirm('Are you sure you want to delete this assignment?')">
                    <i class="fas fa-trash-alt" aria-hidden="true"></i> Delete
                </a>
            </div>
        </div>

        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-icon purple"><i class="fas fa-school" aria-hidden="true"></i></div>
                <div class="metric-data">
                    <span class="metric-label">Target Class</span>
                    <span class="metric-value"><?php echo htmlspecialchars($homework['class_name'] ?? 'N/A'); ?></span>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon orange"><i class="fas fa-calendar-alt" aria-hidden="true"></i></div>
                <div class="metric-data">
                    <span class="metric-label">Due Date</span>
                    <span class="metric-value">
                        <?php echo $dueDate ? date('M d, Y', $dueDate) : 'No Due Date'; ?>
                    </span>
                    <small class="metric-subtext">
                        <?php echo $dueDate ? date('h:i A', $dueDate) : ''; ?>
                    </small>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon blue"><i class="fas fa-file-invoice" aria-hidden="true"></i></div>
                <div class="metric-data">
                    <span class="metric-label">Submissions Received</span>
                    <span class="metric-value"><?php echo count($submissions); ?></span>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon green"><i class="fas fa-clock" aria-hidden="true"></i></div>
                <div class="metric-data">
                    <span class="metric-label">Created Date</span>
                    <span class="metric-value">
                        <?php echo !empty($homework['created_at']) ? date('M d, Y', strtotime($homework['created_at'])) : 'N/A'; ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="content-grid">
        <div class="main-content-card">
            <h3 class="card-section-title"><i class="fas fa-align-left" aria-hidden="true"></i> Assignment Description</h3>
            <div class="description-body">
                <?php echo nl2br(htmlspecialchars($homework['description'] ?? 'No description provided for this assignment.')); ?>
            </div>

            <?php if (!empty($homework['file_path'])): ?>
                <div class="attachment-section">
                    <div class="attachment-info">
                        <i class="fas fa-paperclip" aria-hidden="true"></i>
                        <div>
                            <strong>Teacher Attachment</strong>
                            <p>Reference files uploaded for student download.</p>
                        </div>
                    </div>
                    <a href="<?php echo BASE_URL . '/' . htmlspecialchars(ltrim($homework['file_path'], '/')); ?>" target="_blank" rel="noopener noreferrer" class="btn-download">
                        <i class="fas fa-download" aria-hidden="true"></i> View File
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div class="table-card">
            <div class="table-card-header">
                <h3><i class="fas fa-users-cog" aria-hidden="true"></i> Student Submissions</h3>
                <span class="badge-counter"><?php echo count($submissions); ?> Total</span>
            </div>
            
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Submitted On</th>
                            <th>Status</th>
                            <th>Score</th>
                            <th>Attachment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($submissions)): ?>
                            <tr>
                                <td colspan="5" class="empty-state">
                                    <i class="fas fa-folder-open" aria-hidden="true"></i>
                                    <p>No student submissions found for this assignment yet.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($submissions as $sub): ?>
                                <tr>
                                    <td>
                                        <div class="student-avatar-cell">
                                            <div class="avatar-circle">
                                                <?php echo htmlspecialchars(strtoupper(substr($sub['first_name'] ?? 'S', 0, 1))); ?>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars(trim(($sub['first_name'] ?? '') . ' ' . ($sub['last_name'] ?? 'Student'))); ?></strong>
                                                <?php if (!empty($sub['text_answer'])): ?>
                                                    <br><small class="text-muted" style="font-style: italic;">"<?php echo htmlspecialchars(mb_strimwidth($sub['text_answer'], 0, 40, '...')); ?>"</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="time-cell">
                                            <?php $subTime = !empty($sub['submitted_at']) ? strtotime($sub['submitted_at']) : 0; ?>
                                            <span><?php echo $subTime ? date('M d, Y', $subTime) : 'N/A'; ?></span>
                                            <small><?php echo $subTime ? date('h:i A', $subTime) : ''; ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                            $subStatus = strtolower($sub['status'] ?? 'submitted');
                                            $statusClass = $subStatus === 'graded' ? 'sub-graded' : ($subStatus === 'late' ? 'sub-disabled' : 'sub-pending');
                                        ?>
                                        <span class="sub-badge <?php echo $statusClass; ?>">
                                            <?php echo htmlspecialchars(ucfirst($subStatus)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="score-text">
                                            <?php echo isset($sub['grade']) && $sub['grade'] !== '' && $sub['grade'] !== null ? htmlspecialchars((string)$sub['grade']) : '<span class="text-muted">--</span>'; ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <?php if (!empty($sub['files']) && is_array($sub['files'])): ?>
                                            <div class="attachment-list">
                                                <?php foreach ($sub['files'] as $file): ?>
                                                    <?php
                                                        $cleanSubName = basename($file['file_path'] ?? ''); 
                                                        $officialSubmissionUrl = "https://docs.raysofgrace.ac.ug/rogele-platform/uploads/submissions/" . rawurlencode($cleanSubName);
                                                        $fileName = $file['file_name'] ?? $cleanSubName;
                                                        $fileSize = isset($file['file_size']) ? round($file['file_size'] / 1024, 1) : 0;
                                                    ?>
                                                    <a href="<?php echo htmlspecialchars($officialSubmissionUrl); ?>" target="_blank" rel="noopener noreferrer" class="attachment-btn" title="<?php echo htmlspecialchars($fileName); ?>">
                                                        <i class="fas fa-paperclip" aria-hidden="true"></i>
                                                        <span class="file-name"><?php echo htmlspecialchars($fileName); ?></span>
                                                        <span class="file-size"><?php echo $fileSize; ?> KB</span>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fas fa-minus" aria-hidden="true"></i> No Attachment</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --primary-purple: #7f2677;
    --accent-orange: #f06724;
    --text-dark: #000;
    --text-muted: #555;
    --bg-surface: #FFFFFF;
    --border-color: #E2E8F0;
    --radius-lg: 20px;
    --radius-md: 12px;
    --shadow-sm: 0 4px 12px rgba(0,0,0,0.03);
    --shadow-md: 0 10px 30px rgba(0,0,0,0.05);
    --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.admin-view-container,
.admin-view-container * {
    box-sizing: border-box;
}

.admin-view-container {
    padding: clamp(16px, 3vw, 32px);
    max-width: 1350px;
    margin: 0 auto;
    color: var(--text-dark);
}

.view-header-card {
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    padding: clamp(20px, 3vw, 30px);
    margin-bottom: clamp(20px, 3vw, 25px);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-color);
}

.header-main {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    padding-bottom: clamp(16px, 2.5vw, 25px);
    border-bottom: 1px solid #F1F5F9;
    flex-wrap: wrap;
}

.category-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #F3E8FF;
    color: var(--primary-purple);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.775rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 10px;
}

.page-title {
    font-size: clamp(1.4rem, 3.5vw, 2rem);
    font-weight: 700;
    color: var(--text-dark);
    margin: 0 0 8px 0;
    line-height: 1.25;
}

.teacher-info {
    color: var(--text-muted);
    font-size: clamp(0.85rem, 2vw, 0.95rem);
    margin: 0;
}

.teacher-info i {
    color: var(--accent-orange);
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 30px;
    font-size: 0.825rem;
    font-weight: 600;
    white-space: nowrap;
}

.status-active { background: #DCFCE7; color: #166534; }
.status-expired { background: #FEF3C7; color: #92400E; }
.status-disabled { background: #F1F5F9; color: var(--text-muted); }

.btn-action-outline, .btn-action-danger {
    padding: 8px 16px;
    border-radius: var(--radius-md);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.85rem;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-action-outline {
    border: 1px solid #CBD5E1;
    background: var(--bg-surface);
    color: var(--text-dark);
}

.btn-action-outline:hover {
    background: #F8FAFC;
    border-color: var(--text-muted);
}

.btn-action-danger {
    background: #FEF2F2;
    color: #DC2626;
}

.btn-action-danger:hover {
    background: #DC2626;
    color: white;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-top: clamp(20px, 3vw, 25px);
}

.metric-card {
    display: flex;
    align-items: center;
    gap: 14px;
    background: #F8FAFC;
    padding: 16px;
    border-radius: var(--radius-md);
    border: 1px solid #F1F5F9;
}

.metric-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.metric-icon.purple { background: #F3E8FF; color: var(--primary-purple); }
.metric-icon.orange { background: #FFF7ED; color: var(--accent-orange); }
.metric-icon.blue { background: #EFF6FF; color: #2563EB; }
.metric-icon.green { background: #ECFDF5; color: #059669; }

.metric-data {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.metric-label {
    font-size: 0.725rem;
    text-transform: uppercase;
    font-weight: 600;
    color: var(--text-muted);
    letter-spacing: 0.5px;
}

.metric-value {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--text-dark);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.metric-subtext {
    font-size: 0.75rem;
    color: var(--text-muted);
}

.content-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: clamp(20px, 3vw, 25px);
}

.main-content-card {
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    padding: clamp(20px, 3vw, 30px);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-color);
}

.card-section-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--text-dark);
    margin-top: 0;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.card-section-title i {
    color: var(--accent-orange);
}

.description-body {
    color: var(--text-dark);
    line-height: 1.7;
    font-size: 0.95rem;
}

.attachment-section {
    margin-top: 24px;
    padding: 16px 20px;
    background: #F8FAFC;
    border: 1px dashed #CBD5E1;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}

.attachment-info {
    display: flex;
    align-items: center;
    gap: 14px;
}

.attachment-info i {
    font-size: 1.4rem;
    color: var(--primary-purple);
}

.attachment-info strong {
    display: block;
    color: var(--text-dark);
    font-size: 0.9rem;
}

.attachment-info p {
    margin: 0;
    font-size: 0.8rem;
    color: var(--text-muted);
}

.btn-download {
    padding: 9px 18px;
    background: var(--primary-purple);
    color: white;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.85rem;
    white-space: nowrap;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-download:hover {
    background: var(--accent-orange);
}

.table-card {
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
    overflow: hidden;
}

.table-card-header {
    padding: 18px 24px;
    border-bottom: 1px solid #F1F5F9;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-card-header h3 {
    margin: 0;
    font-size: 1.05rem;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    gap: 8px;
}

.table-card-header h3 i {
    color: var(--accent-orange);
}

.badge-counter {
    background: #F1F5F9;
    color: var(--text-muted);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 700;
}

.table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: #F8FAFC;
    color: var(--text-dark);
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 14px 20px;
    text-align: left;
    border-bottom: 2px solid var(--border-color);
    white-space: nowrap;
}

.data-table td {
    padding: 14px 20px;
    border-bottom: 1px solid #F1F5F9;
    vertical-align: middle;
}

.data-table tr:hover td {
    background: #F8FAFC;
}

.student-avatar-cell {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 180px;
}

.avatar-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--primary-purple);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
    flex-shrink: 0;
}

.time-cell {
    white-space: nowrap;
}

.time-cell span {
    display: block;
    color: var(--text-dark);
    font-weight: 500;
    font-size: 0.875rem;
}

.time-cell small {
    color: var(--text-muted);
    font-size: 0.775rem;
}

.sub-badge {
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    white-space: nowrap;
}

.sub-graded { background: #ECFDF5; color: #047857; }
.sub-pending { background: #FFFBEB; color: #B45309; }
.sub-disabled { background: #FEF2F2; color: #DC2626; }

.score-text {
    font-size: 0.9rem;
    color: var(--text-dark);
}

.attachment-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 200px;
    max-width: 280px;
}

.attachment-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    background-color: #F8FAFC;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    color: var(--text-dark);
    text-decoration: none;
    font-size: 0.825rem;
    font-weight: 500;
    transition: var(--transition);
    width: 100%;
}

.attachment-btn:hover {
    background-color: #F1F5F9;
    border-color: var(--text-muted);
}

.attachment-btn i {
    color: var(--accent-orange);
    font-size: 0.875rem;
    flex-shrink: 0;
}

.attachment-btn .file-name {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex-grow: 1;
}

.attachment-btn .file-size {
    font-size: 0.725rem;
    color: var(--text-muted);
    background: #E2E8F0;
    padding: 2px 6px;
    border-radius: 4px;
    white-space: nowrap;
    flex-shrink: 0;
}

.empty-state {
    text-align: center;
    padding: 48px 20px !important;
    color: var(--text-muted);
}

.empty-state i {
    font-size: 2.2rem;
    margin-bottom: 10px;
    color: var(--accent-orange);
    opacity: 0.8;
}

.empty-state p {
    margin: 0;
    font-size: 0.9rem;
}

.text-muted {
    color: var(--text-muted);
}

@media (max-width: 768px) {
    .header-main {
        flex-direction: column;
    }

    .header-actions {
        width: 100%;
        justify-content: flex-start;
    }

    .attachment-section {
        flex-direction: column;
        align-items: flex-start;
    }

    .btn-download {
        width: 100%;
        justify-content: center;
    }
}
</style>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>