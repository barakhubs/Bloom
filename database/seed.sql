-- Bloom Beyond Borders seed data
-- Import after schema.sql: mysql -u root -p bloom < database/seed.sql
--
-- Placeholder content below mirrors what is *currently live* on bloombeyondborders.org
-- today (per CLAUDE.md: "seed these with the current live-site placeholder text as
-- starting content"), including sections that read as unedited template filler
-- (e.g. "Animals Saved", "Trees Planted" stat labels, the Elliot Smith board letter).
-- Replace all of it via the admin back office once real content is available.

-- --------------------------------------------------
-- admin_users
-- --------------------------------------------------
-- Default login: admin@bloombeyondborders.org / ChangeMe123!
-- CHANGE THIS PASSWORD IMMEDIATELY after first login (feature/admin-auth).
INSERT INTO admin_users (email, password_hash) VALUES
    ('admin@bloombeyondborders.org', '$2y$10$wiKAx9tcVkSN2HLPjDWAIOOrcNhlqBHQcWkWiUSVqgGTZ2oqVs6oa');

-- --------------------------------------------------
-- settings
-- --------------------------------------------------
INSERT INTO settings (`key`, `value`) VALUES
    ('contact_phone', '+256 791-269-955'),
    ('contact_email', 'agathaasiimwe@gmail.com'),
    ('contact_address', '1234 Divi St. #1000 San Francisco, CA 91351'),
    ('social_linkedin', ''),   -- TODO: set via /admin/settings
    ('social_instagram', ''),  -- TODO: set via /admin/settings
    ('site_logo_path', ''),
    ('favicon_path', ''),
    ('footer_logo_path', ''),
    ('smtp_host', ''),
    ('smtp_port', '587'),
    ('smtp_username', ''),
    ('smtp_password', ''),
    ('smtp_encryption', 'tls'),
    ('smtp_from_email', 'no-reply@bloombeyondborders.org'),
    ('smtp_from_name', 'Bloom Beyond Borders');

-- --------------------------------------------------
-- team_members — Board of Directors, from the live "Our Team" page
-- --------------------------------------------------
INSERT INTO team_members (name, title, bio, sort_order) VALUES
    ('Agatha Asiimwe, EdD', 'Executive Director and Founder',
     'Human services professional and educator with 8+ years serving people with intellectual, developmental disabilities, and mental illness. Nine years teaching experience at high school and college levels. Ed.D. in Educational Leadership from Washington State University. Focuses on social justice, cross-cultural understanding, and empowering marginalized groups. Mother of three who enjoys gardening and cooking.',
     1),
    ('Agatha Ampaire, PhD', 'Director and Co-founder',
     'Higher education professional with 5+ years in academic advising and student success. Academic Advisor at Prairie View A&M University. Ph.D. in Animal Science and M.Ed. in Counseling from South Dakota State University. Specializes in academic planning, student retention strategies, financial literacy, and inclusive learning environments.',
     2),
    ('Rosetta Adzasu, MS', 'Director and Co-founder',
     'Human services professional specializing in crisis intervention and trauma-informed care. Case Manager for State of Oregon in Multnomah County. Master''s in Psychology from Concordia University; Bachelor''s in Criminal Justice from Washington State University. Multilingual in English, Italian, Spanish, Ewe, and Twi.',
     3),
    ('Bishop Pons Awinjo Ozelle', 'Director / Board Chair',
     '3rd Bishop of Nebbi Diocese, Uganda (fourth term). Master''s in Theology from Trinity Theological University (Singapore). Ordained 2000. Former educator and footballer. Strong advocate for education, youth empowerment, and moral integrity.',
     4),
    ('Faith Mbabazi Musinguzi, PhD', 'Director',
     'Educator and counselor with 20+ years in higher education. Head of Postgraduate Studies at Uganda Christian University. Ph.D. in Education researching administrative burnout. Also leads Agape Wellness Counseling Firm. Expertise in educational psychology, guidance and counseling, and mental wellness.',
     5),
    ('Alvis Asiimwe', 'Director',
     'Young aspiring nurse and childcare provider from Ridgefield, Washington. 2025 high school graduate completing nursing prerequisites at Clark College. Works as IHOP hostess and nursery attendant. Deeply committed to social justice and community service.',
     6);

-- --------------------------------------------------
-- story_stats — Our Story impact-stats dashboard
-- Labels match the live site; values are unset placeholders (real numbers TODO).
-- --------------------------------------------------
INSERT INTO story_stats (label, value, sort_order) VALUES
    ('Dollars Raised', '0', 1),
    ('Volunteers', '0', 2),
    ('Contributors', '0', 3),
    ('Animals Saved', '0', 4),
    ('Trees Planted', '0', 5),
    ('Cities', '0', 6),
    ('Trained Farmers', '0', 7),
    ('Years', '0', 8);

-- --------------------------------------------------
-- story_finance_entries — Our Story financial-allocation chart
-- Live site shows 2015-2018 by category with no visible real percentages;
-- seeded here as an even placeholder split for 2018, TODO real figures via admin.
-- --------------------------------------------------
INSERT INTO story_finance_entries (year, category, percentage, sort_order) VALUES
    (2018, 'Campaigns', 25.00, 1),
    (2018, 'Research & Development', 25.00, 2),
    (2018, 'Management', 25.00, 3),
    (2018, 'Organization Growth', 25.00, 4);

-- --------------------------------------------------
-- story_board_letter — placeholder Lorem Ipsum letter as currently live on the site
-- --------------------------------------------------
INSERT INTO story_board_letter (id, author_name, author_title, body) VALUES
    (1, 'Elliot Smith', 'Board Member',
     'Lorem ipsum dolor sit amet, consectetur adipiscing elit. This placeholder letter mirrors what is currently live on bloombeyondborders.org and should be replaced with a real board letter via the admin back office.');
