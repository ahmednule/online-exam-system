<?php
$currentPage = $page ?? 'home';
$role = $_SESSION['role'];

$items = $role === 'admin'
    ? [
        ['page' => 'home',  'icon' => 'bi-speedometer2',    'label' => 'Dashboard'],
        ['page' => 'courses', 'icon' => 'bi-book',         'label' => 'Courses'],
        ['page' => 'units',  'icon' => 'bi-journal-text',  'label' => 'Units'],
        ['page' => 'questions', 'icon' => 'bi-question-circle', 'label' => 'Question Bank'],
        ['page' => 'attempts', 'icon' => 'bi-clipboard-data','label' => 'Attempts'],
        ['page' => 'analytics','icon' => 'bi-graph-up',     'label' => 'Analytics'],
    ]
    : [
        ['page' => 'home',   'icon' => 'bi-speedometer2',    'label' => 'Dashboard'],
        ['page' => 'units',  'icon' => 'bi-journal-text',    'label' => 'My Units'],
        ['page' => 'results','icon' => 'bi-trophy',          'label' => 'My Results'],
        ['page' => 'profile','icon' => 'bi-person-circle',   'label' => 'Profile'],
    ];
?>

<ul class="dash-nav-list">
    <?php foreach ($items as $item): ?>
        <li>
            <a href="?page=<?= $item['page'] ?>" class="dash-nav-link <?= $currentPage === $item['page'] ? 'active' : '' ?>">
                <i class="bi <?= $item['icon'] ?>"></i>
                <span><?= $item['label'] ?></span>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
