<?php
// File: /views/admin/homework/view.php
$pageTitle = 'Homework Details | ROGELE';
require_once __DIR__ . '/../../layouts/admin_header.php';

$homework = $homework ?? [];
$submissions = $submissions ?? [];
?>

<div class="admin-view-container">

    <div class="view-header-card">
        <div class="header-main">
            <div class="title-area">
                <span class="category-badge">
                    <i class="fas fa-book"></i> <?php echo htmlspecialchars($homework['subject_name'] ?? 'General Subject'); ?>
                </span>
                <h1 class="page-title"><?php echo htmlspecialchars($homework['title'] ?? 'Homework Details'); ?></h1>
                <p class="teacher-info">
                    <i class="fas fa-chalkboard-teacher"></i> Assigned by <strong><?php echo htmlspecialchars($homework['teacher_name'] ?? 'Unknown Teacher'); ?></strong>
                </p>
            </div>
            
            <div class="header-actions">
                <?php 
                    $isExpired = strtotime($homework['due_date'] ?? 'now') < time();
                    $isActive = (bool)($homework['is_active'] ?? false);
                ?>
                <?php if (!$isActive): ?>
                    <span class="status-pill status-disabled"><i class="fas fa-ban"></i> Disabled</span>
                <?php elseif ($isExpired): ?>
                    <span class="status-pill status-expired"><i class="fas fa-clock"></i> Expired</span>
                <?php else: ?>
                    <span class="status-pill status-active"><i class="fas fa-check-circle"></i> Active</span>
                <?php endif; ?>

                <a href="<?php echo BASE_URL; ?>/admin/homework/toggle-status/<?php echo $homework['id']; ?>" 
                   class="btn-action-outline" 
                   onclick="return confirm('Toggle status for this assignment?')">
                    <i class="fas <?php echo $isActive ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                    <?php echo $isActive ? 'Disable' : 'Enable'; ?>
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/homework/delete/<?php echo $homework['id']; ?>" 
                   class="btn-action-danger" 
                   onclick="return confirm('Are you sure you want to delete this assignment?')">
                    <i class="fas fa-trash-alt"></i> Delete
                </a>
            </div>
        </div>

        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-icon purple"><i class="fas fa-school"></i></div>
                <div class="metric-data">
                    <span class="metric-label">Target Class</span>
                    <span class="metric-value"><?php echo htmlspecialchars($homework['class_name'] ?? 'N/A'); ?></span>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon orange"><i class="fas fa-calendar-alt"></i></div>
                <div class="metric-data">
                    <span class="metric-label">Due Date</span>
                    <span class="metric-value">
                        <?php echo !empty($homework['due_date']) ? date('M d, Y', strtotime($homework['due_date'])) : 'No Due Date'; ?>
                    </span>
                    <small class="metric-subtext">
                        <?php echo !empty($homework['due_date']) ? date('h:i A', strtotime($homework['due_date'])) : ''; ?>
                    </small>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon blue"><i class="fas fa-file-invoice"></i></div>
                <div class="metric-data">
                    <span class="metric-label">Submissions Received</span>
                    <span class="metric-value"><?php echo count($submissions); ?></span>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon green"><i class="fas fa-clock"></i></div>
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
            <h3 class="card-section-title"><i class="fas fa-align-left"></i> Assignment Description</h3>
            <div class="description-body">
                <?php echo nl2br(htmlspecialchars($homework['description'] ?? 'No description provided for this assignment.')); ?>
            </div>

            <?php if (!empty($homework['file_path'])): ?>
                <div class="attachment-section">
                    <div class="attachment-info">
                        <i class="fas fa-paperclip"></i>
                        <div>
                            <strong>Teacher Attachment</strong>
                            <p>Reference files uploaded for student download.</p>
                        </div>
                    </div>
                    <a href="<?php echo BASE_URL . '/' . htmlspecialchars($homework['file_path']); ?>" target="_blank" class="btn-download">
                        <i class="fas fa-download"></i> View File
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div class="table-card">
            <div class="table-card-header">
                <h3><i class="fas fa-users-cog"></i> Student Submissions</h3>
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
                                    <i class="fas fa-folder-open"></i>
                                    <p>No student submissions found for this assignment yet.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($submissions as $sub): ?>
                                <tr>
                                    <td>
                                        <div class="student-avatar-cell">
                                            <div class="avatar-circle">
                                                <?php echo strtoupper(substr($sub['first_name'] ?? 'S', 0, 1)); ?>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars(($sub['first_name'] ?? '') . ' ' . ($sub['last_name'] ?? 'Student')); ?></strong>
                                                <?php if (!empty($sub['text_answer'])): ?>
                                                    <br><small class="text-muted" style="font-style: italic;">"<?php echo htmlspecialchars(mb_strimwidth($sub['text_answer'], 0, 40, '...')); ?>"</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="time-cell">
                                            <span><?php echo !empty($sub['submitted_at']) ? date('M d, Y', strtotime($sub['submitted_at'])) : 'N/A'; ?></span>
                                            <small><?php echo !empty($sub['submitted_at']) ? date('h:i A', strtotime($sub['submitted_at'])) : ''; ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                            $subStatus = strtolower($sub['status'] ?? 'submitted');
                                            $statusClass = $subStatus === 'graded' ? 'sub-graded' : ($subStatus === 'late' ? 'sub-disabled' : 'sub-pending');
                                        ?>
                                        <span class="sub-badge <?php echo $statusClass; ?>">
                                            <?php echo ucfirst($subStatus); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="score-text">
                                            <?php echo isset($sub['grade']) && $sub['grade'] !== '' && $sub['grade'] !== null ? htmlspecialchars($sub['grade']) : '<span class="text-muted">--</span>'; ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <?php if (!empty($sub['files'])): ?>
                                            <?php foreach ($sub['files'] as $file): ?>
                                                <a href="<?php echo htmlspecialchars($file['file_path']); ?>" target="_blank" class="file-link-btn" title="<?php echo htmlspecialchars($file['file_name']); ?>">
                                                    <i class="fas fa-file-download"></i> View File
                                                </a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted"><i class="fas fa-minus"></i> No Attachment</span>
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
.admin-view-container {
    padding: 30px 20px;
    max-width: 1350px;
    margin: 0 auto;
    font-family: 'Inter', sans-serif;
    color: #1E293B;
}

.view-header-card {
    background: #FFFFFF;
    border-radius: 16px;
    padding: 25px 30px;
    margin-bottom: 25px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    border: 1px solid #E2E8F0;
}

.header-main {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    padding-bottom: 25px;
    border-bottom: 1px solid #F1F5F9;
}

.category-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #F3E8FF;
    color: #7f2677;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 10px;
}

.page-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #000;
    margin: 0 0 8px 0;
    line-height: 1.25;
}

.teacher-info {
    color: #555;
    font-size: 0.95rem;
    margin: 0;
}

.teacher-info i {
    color: #f06724;
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
    font-size: 0.85rem;
    font-weight: 600;
}

.status-active { background: #DCFCE7; color: #166534; }
.status-expired { background: #FEF3C7; color: #92400E; }
.status-disabled { background: #F1F5F9; color: #555; }

.btn-action-outline {
    padding: 8px 16px;
    border: 1px solid #CBD5E1;
    background: white;
    color: #555;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.85rem;
    transition: all 0.2s ease;
}

.btn-action-outline:hover {
    background: #F8FAFC;
    border-color: #555;
}

.btn-action-danger {
    padding: 8px 16px;
    background: #FEF2F2;
    color: #DC2626;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.85rem;
    transition: all 0.2s ease;
}

.btn-action-danger:hover {
    background: #DC2626;
    color: white;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-top: 25px;
}

.metric-card {
    display: flex;
    align-items: center;
    gap: 15px;
    background: #F8FAFC;
    padding: 16px 20px;
    border-radius: 12px;
    border: 1px solid #F1F5F9;
}

.metric-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.metric-icon.purple { background: #F3E8FF; color: #7f2677; }
.metric-icon.orange { background: #FFF7ED; color: #f06724; }
.metric-icon.blue { background: #EFF6FF; color: #2563EB; }
.metric-icon.green { background: #ECFDF5; color: #059669; }

.metric-data {
    display: flex;
    flex-direction: column;
}

.metric-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    font-weight: 600;
    color: #555;
    letter-spacing: 0.5px;
}

.metric-value {
    font-size: 1.1rem;
    font-weight: 700;
    color: #000;

}

.metric-subtext {
    font-size: 0.75rem;
    color: #555;
}

.content-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 25px;
}

.main-content-card {
    background: white;
    border-radius: 16px;
    padding: 25px 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    border: 1px solid #E2E8F0;
}

.card-section-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #000;
    margin-top: 0;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-section-title i {
    color: #f06724;
}

.description-body {
    color: #555;
    line-height: 1.7;
    font-size: 0.98rem;
}

.attachment-section {
    margin-top: 25px;
    padding: 15px 20px;
    background: #F8FAFC;
    border: 1px dashed #CBD5E1;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
}

.attachment-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.attachment-info i {
    font-size: 1.5rem;
    color: #7f2677;
}

.attachment-info strong {
    display: block;
    color: #000;
    font-size: 0.95rem;
}

.attachment-info p {
    margin: 0;
    font-size: 0.82rem;
    color: #555;
}

.btn-download {
    padding: 9px 18px;
    background: #7f2677;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.85rem;
    white-space: nowrap;
    transition: background 0.2s ease;
}

.btn-download:hover {
    background: #f06724;
}

.table-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    border: 1px solid #E2E8F0;
    overflow: hidden;
}

.table-card-header {
    padding: 20px 30px;
    border-bottom: 1px solid #F1F5F9;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-card-header h3 {
    margin: 0;
    font-size: 1.1rem;
    color: #000;
    display: flex;
    align-items: center;
    gap: 10px;
}

.table-card-header h3 i {
    color: #f06724;
}

.badge-counter {
    background: #F1F5F9;
    color: #555;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 700;
}

.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: #F8FAFC;
    color: #555;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 14px 25px;
    text-align: left;
    border-bottom: 1px solid #E2E8F0;
}

.data-table td {
    padding: 16px 25px;
    border-bottom: 1px solid #F1F5F9;
    vertical-align: middle;
}

.data-table tr:last-child td {
    border-bottom: none;
}

.data-table tr:hover td {
    background: #F8FAFC;
}

.student-avatar-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.avatar-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #7f2677;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
}

.time-cell span {
    display: block;
    color: #000;
    font-weight: 500;
    font-size: 0.9rem;
}

.time-cell small {
    color: #555;
    font-size: 0.78rem;
}

.sub-badge {
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
}

.sub-graded { background: #ECFDF5; color: #047857; }
.sub-pending { background: #FFFBEB; color: #B45309; }

.score-text {
    font-size: 0.95rem;
    color: #000;
}

.file-link-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #EFF6FF;
    color: #2563EB;
    border-radius: 6px;
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.2s ease;
}

.file-link-btn:hover {
    background: #DBEAFE;
}

.empty-state {
    text-align: center;
    padding: 40px 20px !important;
    color: #555;
}

.empty-state i {
    font-size: 2.2rem;
    margin-bottom: 10px;
    color: #f06724;
}

.empty-state p {
    margin: 0;
    font-size: 0.95rem;
}

.text-muted {
    color: #555;
}

@media (max-width: 900px) {
    .header-main {
        flex-direction: column;
    }

    .header-actions {
        width: 100%;
        justify-content: flex-start;
    }
}

@media (max-width: 600px) {
    .attachment-section {
        flex-direction: column;
        align-items: flex-start;
    }

    .btn-download {
        width: 100%;
        text-align: center;
    }
}
</style>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>