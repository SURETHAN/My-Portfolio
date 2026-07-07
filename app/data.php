<?php

declare(strict_types=1);

if (!defined('APP_BOOT')) {
    http_response_code(403);
    exit('Forbidden');
}

/**
 * Single source of truth for all site content.
 * Every number is verified from git history / the portfolio brief.
 * Items marked [ADD ...] are placeholders awaiting confirmation.
 */
return [

    'identity' => [
        'name'      => 'Surethan S',
        'nameMain'  => 'SURETHAN',
        'nameTail'  => 'S',
        'roles'     => ['Product Developer', 'Frappe / ERPNext Engineer', 'AI Integrations', 'MCP & Agent Builder'],
        'tagline'   => 'I ship production AI — not demos.',
        'subline'   => 'Voice agents that qualify every sales lead. Multi-agent pipelines that turn university syllabi into living learning platforms. All deployed, all in daily use.',
        'location'  => 'Bengaluru, India',
        'company'   => 'Selfmade Ninja Academy',
        'email'     => 'surethan37@gmail.com',
        'linkedin'  => 'https://www.linkedin.com/in/surethan-s-3865bb385/',
        'github'    => '', // [ADD GITHUB PROFILE URL]
        'coords'    => '12.9716°N / 77.5946°E',
    ],

    'about' => [
        'heading' => 'From lecture halls to production logs.',
        'aside'   => 'Three years into a five-year degree, the pull of real users beat the pull of a diploma.',
        'paragraphs' => [
            "I'm a product developer at Selfmade Ninja Academy in Bengaluru — a tech academy and cloud-labs platform teaching programming, Linux and cybersecurity, with its whole business running on Frappe/ERPNext.",
            'I finished school in Erode, joined the 5-year integrated M.Sc. Software Systems programme at Kongu Engineering College — and after three years, left to build products full-time. Months later, my code was qualifying every sales lead the academy gets and generating lessons for thousands of learners.',
            "My lane is the unglamorous end of AI engineering: prompts that survive noisy Thanglish transcripts, pipelines that never destroy data on failure, telephony that can't deadlock, and attribution that ad platforms actually trust.",
        ],
        'stats' => [
            ['value' => 25, 'suffix' => 'k+', 'label' => 'lines shipped to production in ~3 months'],
            ['value' => 59, 'suffix' => '%',  'label' => "of all commits in the academy's core ERP app"],
            ['value' => 67, 'suffix' => '',   'label' => 'automated tests guarding the AI call system'],
            ['value' => 5,  'suffix' => '',   'label' => 'LLM agents in one production pipeline'],
        ],
    ],

    'skillGroups' => [
        [
            'index' => 'A',
            'title' => 'AI Engineering',
            'blurb' => 'Structured extraction at temperature 0, multi-agent orchestration, prompts hardened against noisy real-world input.',
            'items' => ['OpenAI API', 'OpenAI Agents SDK', 'Pydantic-typed outputs', 'MCP server development', 'n8n agent workflows', 'Prompt design (Thanglish STT)'],
            'featured' => true,
        ],
        [
            'index' => 'B',
            'title' => 'Backend & Platform',
            'blurb' => 'Production Python on Frappe/ERPNext — the framework the whole academy runs on.',
            'items' => ['Python (async pipelines)', 'Frappe / ERPNext v15', 'DocTypes & lifecycle hooks', 'Whitelisted APIs', 'Background jobs & schedulers', 'Patches & fixtures'],
            'featured' => true,
        ],
        [
            'index' => 'C',
            'title' => 'Data & Infrastructure',
            'blurb' => 'Queues, stores and containers that keep long-running AI jobs honest.',
            'items' => ['MongoDB', 'MariaDB / MySQL', 'Redis', 'RabbitMQ (AMQP)', 'Docker + sysbox-runc', 'S3 / MinIO', 'GitLab CI/CD', 'Traefik'],
            'featured' => false,
        ],
        [
            'index' => 'D',
            'title' => 'Frontend & Funnels',
            'blurb' => 'Dependency-free funnels and dashboards that convert and report.',
            'items' => ['JavaScript (vanilla)', 'jQuery / CoreUI', 'PHP (custom MVC)', 'SASS / Grunt', 'Microsoft Clarity'],
            'featured' => false,
        ],
        [
            'index' => 'E',
            'title' => 'Growth & Integrations',
            'blurb' => 'The plumbing between product, payments and ad platforms.',
            'items' => ['Meta Pixel + Conversions API', 'Event dedup & advanced matching', 'Cashfree / PhonePe payments', 'Zenvoice SIP telephony', 'Discord REST API', 'Telegram integrations'],
            'featured' => false,
        ],
        [
            'index' => 'F',
            'title' => 'Documents & PDF',
            'blurb' => 'Turning hostile PDFs into structured data, and data back into pixel-perfect documents.',
            'items' => ['pdfplumber', 'PyMuPDF', 'OCR — pytesseract + pdf2image', 'WeasyPrint rendering'],
            'featured' => false,
        ],
    ],

    'techMarquee' => [
        'Python', 'Frappe', 'ERPNext', 'OpenAI Agents SDK', 'MCP', 'n8n', 'MongoDB', 'Redis',
        'RabbitMQ', 'Docker', 'MariaDB', 'Meta CAPI', 'Cashfree', 'PHP', 'GitLab CI',
        'S3 / MinIO', 'WeasyPrint', 'pdfplumber', 'Traefik', 'Linux',
    ],

    'timeline' => [
        [
            'period' => 'Schooling',
            'title'  => 'The starting point',
            'org'    => 'URC Palaniammal Matric Hr. Sec. School',
            'place'  => 'Erode, Tamil Nadu',
            'body'   => 'Finished schooling in Erode — where the curiosity for making computers do real work began.',
            'tag'    => '',
        ],
        [
            'period' => '3 of 5 years (dropped out)',
            'title'  => 'M.Sc. Software Systems (Integrated)',
            'org'    => 'Kongu Engineering College',
            'place'  => 'Perundurai, Erode',
            'body'   => 'Three years into the 5-year integrated programme, the pull of building real products outgrew the classroom.',
            'tag'    => '',
        ],
        [
            'period' => 'The leap',
            'title'  => 'Left to build full-time',
            'org'    => 'Student → Product Developer',
            'place'  => '',
            'body'   => 'Walked away from the degree to ship software with real users — a bet that paid off within months.',
            'tag'    => 'INFLECTION',
        ],
        [
            'period' => '2025 — Present',
            'title'  => 'Product Developer',
            'org'    => 'Selfmade Ninja Academy',
            'place'  => 'Bengaluru',
            'body'   => "Became the largest contributor to the academy's core ERP app (136 of 230 commits), sole author of the Syllabi AI pipeline, and builder of the AI voice lead-qualification system — all live in production.",
            'tag'    => 'CURRENT',
        ],
    ],

    'projects' => [
        [
            'id'      => 'syllabi-ai',
            'index'   => '001',
            'name'    => 'Syllabi AI',
            'kicker'  => 'Syllabus PDF → living learning platform',
            'summary' => 'An admin drops in a university syllabus PDF; five specialized LLM agents parse it into a structured hierarchy — campus to department to course to unit — and every student can generate an AI lesson from any unit in one click.',
            'role'    => 'Sole author, end-to-end — backend AI pipeline + web frontend. ~60 commits, +10k lines.',
            'impact'  => [
                'Parses multi-hundred-page syllabi into structured courses & units in minutes',
                "Serves the academy's entire student base at labs.selfmade.ninja",
                'SHA-256 file-hash dedup returns cached parses — tokens are never burned twice',
                'OCR fallback (pytesseract + pdf2image) rescues scanned PDFs',
                'Auto-detects link-index PDFs and switches to flat mode — fetches every Google Drive doc asynchronously',
                'Error recovery never destroys old data until a re-parse succeeds',
            ],
            'tech'     => ['Python 3.12', 'OpenAI Agents SDK', 'Pydantic', 'MongoDB', 'RabbitMQ', 'pdfplumber', 'PyMuPDF', 'aiohttp', 'PHP', 'CoreUI'],
            'liveLabel' => 'labs.selfmade.ninja/syllabus',
            'liveUrl'   => 'https://labs.selfmade.ninja/syllabus',
            'flagship'  => true,
            'architecture' => [
                ['label' => 'INGEST',    'detail' => 'PDF upload · mime/size guard · SHA-256 dedup → HTTP 409'],
                ['label' => 'EXTRACT',   'detail' => 'pdfplumber → OCR fallback · hyperlink harvest via PyMuPDF'],
                ['label' => 'AGENTS ×5', 'detail' => 'Syllabus · FlatSyllabus · Unit · WebUnit · Lab — Pydantic contracts'],
                ['label' => 'NORMALIZE', 'detail' => '1NF tree → per-university MongoDB collections · deterministic IDs'],
                ['label' => 'SERVE',     'detail' => 'RabbitMQ daemon · bell notifications · LearnAI lesson deep-links'],
            ],
            'span' => '',
        ],
        [
            'id'      => 'ai-voice',
            'index'   => '002',
            'name'    => 'AI Voice Lead Qualification',
            'kicker'  => 'Every new lead, called by AI within seconds',
            'summary' => 'A new sales lead lands in the CRM and an AI voice agent calls them back within seconds, interviews them in Tamil-English, extracts a structured qualification profile from the transcript, routes hot leads to humans and schedules everyone else — autonomously.',
            'role'    => 'Feature author, end-to-end. ~15 feature commits + 67 automated tests. Python / Frappe.',
            'impact'  => [
                'Qualifies leads as Hot / Warm / Cold / Callback with 11 structured profile fields',
                'Trunk-capacity guard defers overflow calls instead of burning SIP rejects — deadlock-proof by design',
                'Polling state machine survives vocabulary drift, dropped calls and full outages',
                'Retries land inside configured calling windows — a 10 p.m. signup gets called 10:30 a.m. next day',
                'Writes via low-level field updates so it can never trigger CRM validations or Meta CAPI side effects',
                '67 tests prove the AI can never break the human sales workflow',
            ],
            'tech'      => ['Python', 'Frappe', 'OpenAI (gpt-4o-mini, temp 0)', 'Zenvoice SIP', 'JSON mode', 'Cron state machine'],
            'liveLabel' => 'crm.selfmade.ninja · internal',
            'liveUrl'   => '',
            'flagship'  => true,
            'architecture' => [
                ['label' => 'TRIGGER', 'detail' => "after_insert hook — hardened so failure can't roll back lead creation"],
                ['label' => 'DIAL',    'detail' => 'Zenvoice client · dual auth · trunk-capacity guard + stale-call cutoff'],
                ['label' => 'POLL',    'detail' => '5-min cron state machine · status vocabulary normalization'],
                ['label' => 'EXTRACT', 'detail' => 'gpt-4o-mini @ temp 0 · Thanglish-aware prompt · per-question Q&A rows'],
                ['label' => 'ROUTE',   'detail' => 'Hot → human sales · callbacks honor requested slots · retry caps'],
            ],
            'span' => '',
        ],
        [
            'id'      => 'lead-funnel',
            'index'   => '003',
            'name'    => 'Lead Funnel + Meta CAPI',
            'kicker'  => 'Top of the paid-acquisition funnel',
            'summary' => 'Anonymous multi-step eligibility quiz feeding the CRM — with Meta Pixel + Conversions API attribution, browser/server event dedup, ROAS values and full UTM capture. The leads it qualifies are the ones the AI voice agent calls.',
            'role'    => 'Sole author, frontend + backend.',
            'impact'  => [
                '~1,600 lines of dependency-free vanilla JS/CSS quiz engine',
                'fbc/fbp advanced matching + event-ID dedup between Pixel and CAPI',
                'Host-based domain router serves multiple marketing domains from one Frappe site',
            ],
            'tech'      => ['Vanilla JS', 'Frappe', 'Meta Pixel', 'Conversions API', 'Microsoft Clarity'],
            'liveLabel' => 'leads.selfmade.ninja',
            'liveUrl'   => 'https://leads.selfmade.ninja',
            'flagship'  => false,
            'architecture' => [],
            'span'      => 'wide',
        ],
        [
            'id'      => 'certificates',
            'index'   => '004',
            'name'    => 'Certificate Platform',
            'kicker'  => 'Template-driven credential rendering',
            'summary' => 'Visual position picker with rotation, alignment and word-wrap for laying out dynamic fields on certificate artwork — a clean v3 renderer built alongside the untouched legacy system.',
            'role'    => 'Primary author. Two ~300-line classes, 100% authorship.',
            'impact'  => ['Dummy-data preview before issuing', 'Campaign-aware delivery emails', 'Workshop certificates keyed by registration in S3'],
            'tech'      => ['Python', 'Frappe', 'WeasyPrint', 'S3'],
            'liveLabel' => '',
            'liveUrl'   => '',
            'flagship'  => false,
            'architecture' => [],
            'span'      => '',
        ],
        [
            'id'      => 'campaigns',
            'index'   => '005',
            'name'    => 'Campaigns, Payments & Discord',
            'kicker'  => 'Workshop commerce, end to end',
            'summary' => 'Ported the recorded-workshop site from standalone Flask into the Frappe monolith: registration → Cashfree order → webhook → auto Sales Invoice → academy access. Discord roles granted automatically on payment.',
            'role'    => 'Sole/primary author.',
            'impact'  => [
                'Payment lifecycle hooks with idempotent Discord role seeding',
                'Program API v3 — 13 whitelisted endpoints with server-side filter/search/pagination',
                'KYC hardening (replay, race, PII) on a 2,000-line Cashfree module',
            ],
            'tech'      => ['Python', 'Frappe', 'Cashfree', 'Discord REST', 'Webhooks'],
            'liveLabel' => '',
            'liveUrl'   => '',
            'flagship'  => false,
            'architecture' => [],
            'span'      => '',
        ],
        [
            'id'      => 'agents-mcp',
            'index'   => '006',
            'name'    => 'AI Agents, n8n & MCP',
            'kicker'  => 'Assistants that operate real systems',
            'summary' => 'Beyond the flagships: automation agents built with n8n and the OpenAI Agents SDK, and MCP (Model Context Protocol) servers that let LLM assistants drive real infrastructure. [ADD 1–2 CONCRETE n8n / MCP EXAMPLES]',
            'role'    => 'Builder.',
            'impact'  => ['Syllabi AI (001) is the largest Agents-SDK system in production', 'MCP servers exposing internal tooling to LLM assistants'],
            'tech'      => ['n8n', 'OpenAI Agents SDK', 'MCP', 'Python', 'TypeScript'],
            'liveLabel' => '',
            'liveUrl'   => '',
            'flagship'  => false,
            'architecture' => [],
            'span'      => 'wide',
        ],
    ],

    'testimonials' => [
        [
            'quote' => '[TESTIMONIAL] — Ask a colleague at Selfmade Ninja for two sentences about shipping with Surethan, and put it here.',
            'name'  => '[NAME]',
            'role'  => 'Colleague · Selfmade Ninja Academy',
        ],
        [
            'quote' => '[TESTIMONIAL] — A line from someone who uses Syllabi AI or the CRM daily lands harder than any feature list.',
            'name'  => '[NAME]',
            'role'  => 'Product stakeholder',
        ],
        [
            'quote' => '[TESTIMONIAL] — A mentor, instructor or community voice on how fast you went from student to production owner.',
            'name'  => '[NAME]',
            'role'  => 'Mentor',
        ],
    ],

    'navLinks' => [
        ['href' => '#about',      'label' => 'About'],
        ['href' => '#skills',     'label' => 'Skills'],
        ['href' => '#experience', 'label' => 'Path'],
        ['href' => '#work',       'label' => 'Work'],
        ['href' => '#contact',    'label' => 'Contact'],
    ],
];
