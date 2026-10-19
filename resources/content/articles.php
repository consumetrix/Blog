<?php

/*
| Intelligence articles, from the Workflow Spec §6.4. The Spec marked all four as PLACEHOLDER copy; the owner
| confirmed them as accurate on 9 Oct 2026, so they show in production. A record with 'placeholder' => true
| renders only while placeholders are visible.
|
| Schema: number, type (INSIGHT | ANALYSIS | FIELD NOTE | CASE STUDY), title, related_capability,
| outcome_category (stored, not displayed), body (paragraphs), published_at (Y-m-d or null), placeholder.
*/

return [

    'ai-governance-isnt-an-it-problem' => [
        'number' => '01', 'type' => 'INSIGHT', 'placeholder' => false, 'published_at' => null,
        'title' => 'AI Governance Isn’t an IT Problem.',
        'related_capability' => 'AI governance',
        'outcome_category' => 'Governance',
        'body' => [
            'Most organisations hand AI risk to IT because that is where the tools live. But the questions that matter — who is accountable for a model’s decisions, what data it may use, when a human must intervene — are business and board questions.',
            'Ask three things first: who owns each AI use case, what could go wrong for a customer, and who reports it upward. Technology comes after those answers, not before.',
        ],
    ],

    'the-boards-new-technology-responsibility' => [
        'number' => '02', 'type' => 'ANALYSIS', 'placeholder' => false, 'published_at' => null,
        'title' => 'The Board’s New Technology Responsibility.',
        'related_capability' => 'IT governance',
        'outcome_category' => 'Governance',
        'body' => [
            'Technology now sits inside strategy, risk and reputation. Boards that treat it as an operational detail are approving decisions they cannot evaluate.',
            'Effective boards ask for a short, regular technology view: investments, risks, dependencies, and the few decisions only they can make.',
        ],
    ],

    'what-ghanaian-smes-get-wrong-about-digital-transformation' => [
        'number' => '03', 'type' => 'FIELD NOTE', 'placeholder' => false, 'published_at' => null,
        'title' => 'What Ghanaian SMEs Get Wrong About Digital Transformation.',
        'related_capability' => 'Digital transformation',
        'outcome_category' => 'Transform',
        'body' => [
            'Buying tools before defining the problem is the most common pattern: a CRM nobody trusts, a system that duplicates a spreadsheet.',
            'Start with one process that hurts, fix ownership and data, then choose technology. Smaller scope, faster proof, less regret.',
        ],
    ],

    'from-fragmented-systems-to-one-source-of-truth' => [
        'number' => '04', 'type' => 'CASE STUDY', 'placeholder' => false, 'published_at' => null,
        'title' => 'From fragmented systems to one source of truth.',
        'related_capability' => 'CRM & unified comms',
        'outcome_category' => 'Implement',
        'body' => [
            'Five systems, five versions of the customer. The intervention was as much about ownership and data rules as it was about software.',
            'The result was a single view of the customer and a delivery rhythm the client could keep running without us.',
        ],
    ],

];
