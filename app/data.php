<?php

declare(strict_types=1);

if (!defined('APP_BOOT')) {
    http_response_code(403);
    exit('Forbidden');
}

/**
 * Site content.
 *
 * Everything that changes over time (skills, timeline, projects, stats,
 * testimonials) lives in plain JSON files under content/ — edit those,
 * refresh the page, done. See content/README.md for copy-paste recipes.
 *
 * Only rarely-changing structural content (identity, about text, nav)
 * stays in this file.
 */

/**
 * Load a content/<name>.json file. If the file is missing or has a syntax
 * error, the site stays up: we log the problem and return an empty array
 * (that section simply renders empty until the JSON is fixed).
 */
$loadContent = static function (string $name): array {
    $path = CONTENT_DIR . '/' . $name . '.json';
    if (!is_file($path)) {
        error_log("[content] {$name}.json not found at {$path}");
        return [];
    }
    $decoded = json_decode((string) file_get_contents($path), true);
    if (!is_array($decoded)) {
        error_log("[content] {$name}.json is invalid JSON: " . json_last_error_msg());
        return [];
    }
    return $decoded;
};

$skills = $loadContent('skills');

/*
 * Testimonials = manually curated entries (content/testimonials.json)
 * + customer submissions collected via /customer-feedback
 *   (stored server-side in var/testimonials.json; emails never rendered).
 */
$feedbackCards = [];
$feedbackStore = VAR_DIR . '/testimonials.json';
if (is_file($feedbackStore)) {
    $rows = json_decode((string) file_get_contents($feedbackStore), true);
    if (is_array($rows)) {
        foreach ($rows as $row) {
            $feedbackCards[] = [
                'quote' => (string) ($row['quote'] ?? ''),
                'name'  => (string) ($row['name'] ?? ''),
                'role'  => (string) ($row['role'] ?? ''),
            ];
        }
    }
}

return [

    'identity' => [
        'name'      => 'Surethan S',
        'nameMain'  => 'SURETHAN',
        'nameTail'  => 'S',
        'roles'     => ['AI Systems Engineer', 'Product Engineer', 'Prompt Engineer', 'Platform Engineer — Frappe/ERPNext', 'MCP & Agent Developer'],
        'tagline'   => 'I ship production AI — not demos.',
        'subline'   => 'Voice agents that qualify every sales lead. Multi-agent pipelines that turn university syllabi into living learning platforms. All deployed, all in daily use.',
        'location'  => 'Bengaluru, India',
        'company'   => 'Selfmade Ninja Academy',
        'email'     => 'surethan37@gmail.com',
        'linkedin'  => 'https://www.linkedin.com/in/surethan-s-3865bb385/',
        'github'    => 'https://github.com/SURETHAN',
        'coords'    => '12.9716°N / 77.5946°E',
    ],

    'about' => [
        'heading' => 'From lecture halls to production logs.',
        'aside'   => 'Three years into a five-year degree, the pull of real users beat the pull of a diploma.',
        'paragraphs' => [
            "I'm a product developer at Selfmade Ninja Academy in Bengaluru — a tech academy and cloud-labs platform teaching programming, Linux and cybersecurity, with its whole business running on Frappe/ERPNext.",
            'I finished school in Erode, joined the 5-year integrated M.Sc. Software Systems programme at Kongu Engineering College — and after three years, left to build products full-time. Months later, my code was qualifying every sales lead the academy gets and generating lessons for thousands of learners.',
            "My discipline is production-grade AI engineering: prompt systems that hold up against noisy real-world speech, data pipelines that fail safely without losing a record, telephony that never deadlocks, and attribution that ad platforms verify and trust.",
        ],
        'stats' => $loadContent('stats'),
    ],

    'skillGroups' => $skills['groups'] ?? [],
    'techMarquee' => $skills['marquee'] ?? [],

    'timeline' => $loadContent('timeline'),

    'projects' => $loadContent('projects'),

    'testimonials' => array_merge($loadContent('testimonials'), $feedbackCards),

    'navLinks' => [
        ['href' => '#about',      'label' => 'About'],
        ['href' => '#skills',     'label' => 'Skills'],
        ['href' => '#experience', 'label' => 'Path'],
        ['href' => '#work',       'label' => 'Work'],
        ['href' => '#contact',    'label' => 'Contact'],
    ],
];
