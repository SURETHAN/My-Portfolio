# Editing your portfolio content — no code needed

Everything on the site that changes over time lives in this folder as plain JSON.

| File | What it controls | Section on site |
|---|---|---|
| `stats.json` | The 4 big counters (25k+, 59%, …) | About |
| `skills.json` | Skill cards + the scrolling tech ticker | Capabilities |
| `timeline.json` | Your path / journey entries | Journey |
| `projects.json` | Your work / project cards | Selected work |
| `testimonials.json` | Quotes you add by hand | Endorsements |
| `feedback-access.json` | Emails allowed to use the private feedback form | — |

## The workflow (3 steps)

1. Edit a JSON file in this folder.
2. Check it: `./scripts/check-content.sh`
3. Refresh the browser. **That's it — no rebuild, no restart.**

If a file has a JSON mistake, the site stays up — that one section just
renders empty, and the error is written to the container log
(`docker logs surethan-portfolio-web-1`). Fix the file and refresh.

> On the production server: `git pull` (or copy the changed file), refresh.

## JSON rules (the only 3 that matter)

- Text goes in `"double quotes"`. If your text contains a `"`, write `\"`.
- Items are separated by commas — but **no comma after the last item**.
- Keep every field present; use `""` (empty text) or `[]` (empty list) when you have nothing.

---

## Change a counter (stats.json)

Each stat is one block. `value` is the number that animates, `suffix` is what's
glued after it (`"k+"`, `"%"`, or `""` for nothing):

```json
{ "value": 40, "suffix": "k+", "label": "lines of production code shipped" }
```

Change the numbers/labels freely. You can also add a 5th/6th stat or delete one —
the grid adapts (it looks best with an even count).

## Add a skill (skills.json)

To add one chip to an existing card, add a line to its `"items"` list:

```json
"items": [
  "Prompt engineering",
  "My new skill"        ← added (note the comma on the line above)
]
```

To add a whole new card, copy this block into the `"groups"` list:

```json
{
  "title": "New Discipline",
  "blurb": "One or two sentences, first person, about what you can do.",
  "items": ["Skill one", "Skill two", "Skill three"],
  "featured": false
}
```

`"featured": true` shows the green **Core** badge.
The `"marquee"` list at the bottom is the scrolling ticker — add names freely.

## Add a path entry (timeline.json)

Copy this block to the **end** of the list (order on the page = order in the file):

```json
{
  "period": "2027 — Present",
  "title": "Senior Product Engineer",
  "org": "Company Name",
  "place": "City",
  "body": "One or two sentences about what you built or owned there.",
  "tag": "CURRENT"
}
```

`tag` shows as a small badge — use `"CURRENT"`, `"INFLECTION"`, or `""` for none.
(Only one entry should carry `"CURRENT"` — remove it from the old one.)

## Add a project (projects.json)

Minimal card (fine for most work):

```json
{
  "id": "my-project",
  "index": "007",
  "name": "My New Project",
  "kicker": "One-line hook for the card header",
  "summary": "Two or three sentences on what it does and why it matters.",
  "role": "Sole author.",
  "impact": ["Concrete outcome one", "Concrete outcome two"],
  "tech": ["Python", "Docker"],
  "liveLabel": "myproject.example.com",
  "liveUrl": "https://myproject.example.com",
  "flagship": false,
  "architecture": [],
  "span": ""
}
```

- `index`: just the next number — `"007"`, `"008"`, …
- `liveUrl`: `""` if there is no public link (`liveLabel` still shows as text).
- `flagship: true`: big card with the architecture breakdown.
- `architecture`: only for flagships — list of `{ "label": "STEP", "detail": "what happens" }` rows.
- `span`: `"wide"` makes the card span wider in the grid; `""` for normal.

## Collect customer feedback (the private form)

The site has a **hidden feedback form** at `/customer-feedback`. It is not
linked anywhere — only people you send the URL to will ever see it, and only
emails you authorize can submit it. The whole flow:

1. Add the customer's email to `feedback-access.json`:

   ```json
   [
     "customer@company.com",
     "another@client.io"
   ]
   ```

2. Send them the link: `https://surethan.zeal.ninja/customer-feedback`
3. They fill in their name, **the same email you authorized**, role and feedback.
   Anyone else — or any other email — is rejected. Each email can submit
   **exactly once**.
4. The moment they submit, their card appears in the **Endorsements** section,
   and you get a notification email.

The Endorsements section is **completely hidden** until the first feedback
exists — no placeholders shown to visitors.

Submissions are stored in the server volume (`var/testimonials.json` inside
the container). To view or moderate them:

```bash
docker exec surethan-portfolio-web-1 cat /var/www/html/var/testimonials.json
```

To remove a bad submission, edit that file the same way (`docker exec -it
surethan-portfolio-web-1 sh`, then edit), or ask Claude to do it.

## Add a testimonial by hand (testimonials.json)

For quotes you collected outside the form (a Slack message, a LinkedIn
recommendation), add a card directly:

```json
{
  "quote": "Two sentences from a real person about shipping with you.",
  "name": "Person Name",
  "role": "Their role · Company"
}
```

These render together with form submissions (hand-added ones first).

---

## Command cheat-sheet (jq — refuses to write broken JSON)

All commands run from the project root: `cd /home/labs/surethan-portfolio`
After any change: `./scripts/check-content.sh` then refresh the browser.

```bash
# ---- Feedback emails -------------------------------------------------
# authorize a customer
jq '. + ["customer@company.com"]' content/feedback-access.json > /tmp/j && mv /tmp/j content/feedback-access.json
# list authorized
jq . content/feedback-access.json
# remove one
jq 'map(select(. != "customer@company.com"))' content/feedback-access.json > /tmp/j && mv /tmp/j content/feedback-access.json

# ---- Skills ----------------------------------------------------------
# add a chip to a card (match by card title)
jq '(.groups[] | select(.title == "AI Engineering") | .items) += ["New Skill"]' content/skills.json > /tmp/j && mv /tmp/j content/skills.json
# add to the scrolling ticker
jq '.marquee += ["New Tech"]' content/skills.json > /tmp/j && mv /tmp/j content/skills.json

# ---- Timeline --------------------------------------------------------
jq '. + [{"period": "2027 — Present", "title": "Role", "org": "Company",
  "place": "City", "body": "What you built.", "tag": "CURRENT"}]' \
  content/timeline.json > /tmp/j && mv /tmp/j content/timeline.json
# clear CURRENT from the previous entry (match its title)
jq '(.[] | select(.title == "Product Engineer") | .tag) = ""' content/timeline.json > /tmp/j && mv /tmp/j content/timeline.json

# ---- Projects --------------------------------------------------------
jq '. + [{"id": "my-project", "index": "007", "name": "My Project",
  "kicker": "One-line hook", "summary": "What it does.", "role": "Sole author.",
  "impact": ["Outcome"], "tech": ["Python"], "liveLabel": "", "liveUrl": "",
  "flagship": false, "architecture": [], "span": ""}]' \
  content/projects.json > /tmp/j && mv /tmp/j content/projects.json

# ---- Stats ([0] = first counter, [1] = second, ...) -------------------
jq '.[0].value = 40' content/stats.json > /tmp/j && mv /tmp/j content/stats.json
jq '.[0].label = "new label text"' content/stats.json > /tmp/j && mv /tmp/j content/stats.json

# ---- Feedback submissions (server-side) ------------------------------
# view all
docker exec surethan-portfolio-web-1 cat /var/www/html/var/testimonials.json
```

