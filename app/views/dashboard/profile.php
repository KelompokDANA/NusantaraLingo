<div class="profile-container">
    <div class="profile-header">
        <img src="/public/images/avatars/<?php echo htmlspecialchars($user['avatar'] ?? 'default.png'); ?>" alt="Avatar Pengguna">
        <h2><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></h2>
        <p>@<?php echo htmlspecialchars($user['username']); ?></p>
        <div class="profile-badges">
            <span class="badge badge-primary"><i class="fa fa-star icon"></i> Level <?php echo htmlspecialchars($user['level'] ?? 1); ?></span>
            <span class="badge badge-success"><i class="fa fa-coins icon"></i> <?php echo htmlspecialchars($user['total_points'] ?? 0); ?> Poin</span>
            <span class="badge badge-warning"><i class="fa fa-fire icon"></i> <?php echo htmlspecialchars($user['streak_days'] ?? 0); ?> Hari Streak</span>
        </div>
        <p>Bergabung sejak: <?php echo htmlspecialchars(date('d M Y', strtotime($user['created_at'] ?? ''))); ?></p>
    </div>

    <div class="profile-section">
        <h3>Statistik Umum</h3>
        <div class="card-body">
            <p>Total Kuis Selesai: <strong><?php echo htmlspecialchars($userStats['total_quizzes'] ?? 0); ?></strong></p>
            <p>Rata-rata Skor Kuis: <strong><?php echo htmlspecialchars(number_format($userStats['avg_score'] ?? 0, 2)); ?>%</strong></p>
            <p>Bahasa Dipelajari: <strong><?php echo htmlspecialchars($userStats['languages_count'] ?? 0); ?></strong></p>
            <p>Pencapaian Diraih: <strong><?php echo htmlspecialchars($userStats['achievements_count'] ?? 0); ?></strong></p>
        </div>
    </div>

    <div class="profile-section">
        <h3>Riwayat Kuis</h3>
        <?php if (!empty($quizHistory)): ?>
            <table class="quiz-history-table">
                <thead>
                    <tr>
                        <th>Kuis</th>
                        <th>Bahasa</th>
                        <th>Kategori</th>
                        <th>Skor</th>
                        <th>Persentase</th>
                        <th>Selesai Pada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quizHistory as $attempt): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($attempt['quiz_title']); ?></td>
                            <td><?php echo htmlspecialchars($attempt['language_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($attempt['category_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($attempt['score']); ?></td>
                            <td class="<?php echo ($attempt['percentage'] >= 70) ? 'score-success' : 'score-fail'; ?>"><?php echo htmlspecialchars(number_format($attempt['percentage'], 2)); ?>%</td>
                            <td><?php echo htmlspecialchars(date('d M Y H:i', strtotime($attempt['completed_at']))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Anda belum menyelesaikan kuis apapun.</p>
        <?php endif; ?>
    </div>

    <div class="profile-section">
        <h3>Pencapaian Anda</h3>
        <?php if (!empty($userAchievements)): ?>
            <div class="achievement-grid">
                <?php foreach ($userAchievements as $achievement): ?>
                    <div class="achievement-card <?php echo !empty($achievement['earned_at']) ? 'earned' : 'not-earned'; ?>">
                        <i class="fa fa-<?php echo htmlspecialchars($achievement['icon']); ?> icon"></i>
                        <h4><?php echo htmlspecialchars($achievement['name']); ?></h4>
                        <p><?php echo htmlspecialchars($achievement['description']); ?></p>
                        <?php if (!empty($achievement['earned_at'])): ?>
                            <small>Diraih: <?php echo htmlspecialchars(date('d M Y', strtotime($achievement['earned_at']))); ?></small>
                        <?php else: ?>
                            <small>Belum Diraih</small>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Anda belum meraih pencapaian apapun.</p>
        <?php endif; ?>
    </div>

    <div class="profile-section">
        <h3>Progres Bahasa</h3>
        <?php if (!empty($userProgress)): ?>
            <ul class="progress-list">
                <?php foreach ($userProgress as $progress_item): ?>
                    <li class="progress-item">
                        <div class="progress-text">
                            <span><strong><?php echo htmlspecialchars($progress_item['language_name'] ?? 'N/A'); ?></strong> - <?php echo htmlspecialchars($progress_item['category_name'] ?? 'N/A'); ?></span>
                            <span><?php echo htmlspecialchars(number_format($progress_item['progress_percentage'], 2)); ?>%</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar" style="width: <?php echo htmlspecialchars($progress_item['progress_percentage']); ?>%;" data-percentage="<?php echo htmlspecialchars($progress_item['progress_percentage']); ?>"></div>
                        </div>
                        <small>Aktivitas Terakhir: <?php echo htmlspecialchars(date('d M Y H:i', strtotime($progress_item['last_activity']))); ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Anda belum memulai progres belajar bahasa apapun.</p>
        <?php endif; ?>
    </div>
</div>
