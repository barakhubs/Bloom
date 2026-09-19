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
    ('contact_email', 'info@bloombeyondborders.org'),
    ('contact_address', '1234 Divi St. #1000 San Francisco, CA 91351'),
    ('social_linkedin', 'https://www.linkedin.com/company/bloom-beyond-borders/'),
    ('social_instagram', 'https://www.instagram.com/blooombeyondborders/'),
    ('social_facebook', 'https://www.facebook.com/profile.php?id=61583713474136'),
    ('social_x', 'https://x.com/BloomBB'),
    ('whatsapp_us', '+1 360-803-5283'),
    ('whatsapp_ug', '+256 749 257996'),
    ('site_logo_path', 'img/site-logo.png'),
    ('favicon_path', 'img/favicon.png'),
    ('footer_logo_path', 'img/site-logo.png'),
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
INSERT INTO team_members (name, title, bio, photo_path, sort_order) VALUES
    ('Agatha Asiimwe, EdD', 'Executive Director and Founder',
     'Dr. Agatha Asiimwe is currently a BH Data & Accountability Analyst with the Oregon Health Authority. She is a skilled human services professional and educator who has worked with people with intellectual and developmental disabilities and mental illness in various settings for over 8 years, and has taught for nine years at both the high school and college levels. She recently earned her Doctor of Education (Ed.D.) in Educational Leadership from Washington State University, Vancouver. Social justice, cross-cultural understanding, and empowering marginalized groups are deeply rooted in her work, both professionally and academically. Beyond her professional duties, she is a dedicated mother of three who enjoys spending time with her family, baking, cooking, and tending to her flower and herb garden when the weather permits.',
     'img/team-agatha-asiimwe.jpg', 1),
    ('Agatha Ampaire, PhD', 'Director and Co-founder',
     'Dr. Agatha Ampaire is a dedicated higher education professional and community advocate with over five years of experience in academic advising, career coaching, and student success initiatives. She currently works at Prairie View A&M University supporting diverse student populations. Her educational credentials include a Ph.D. in Animal Science and an M.Ed. in Counseling and Human Development from South Dakota State University, as well as an M.S. in Animal Science from Iowa State University. Her professional strengths encompass academic planning, student retention, financial literacy, and inclusive learning environments, with proficiency in EAB Navigate and DegreeWorks tools while maintaining FERPA compliance. Beyond her university roles, she focuses on virtual coaching, career readiness programs, and community leadership.',
     'img/team-agatha-ampaire.jpg', 2),
    ('Rosetta Adzasu, MS', 'Director and Co-founder',
     'Rosetta Adzasu is a compassionate and dedicated human services professional with extensive experience in crisis intervention, trauma-informed care, and resource coordination. She works as a Case Manager for the State of Oregon in Multnomah County, where she supports individuals and families in navigating complex challenges by connecting them with essential community services and developing individualized plans for self-sufficiency. Her career spans property liaison, child welfare specialist, and seasonal case manager roles at Genentech. She holds a Master of Science in Psychology from Concordia University and a Bachelor of Science in Criminal Justice and Comparative Ethnic Studies from Washington State University. She is fluent in English, Italian, and intermediate Spanish, and speaks Ewe and Twi.',
     'img/team-rosetta-adzasu.jpg', 3),
    ('Bishop Pons Awinjo Ozelle', 'Director / Board Chair',
     'Bishop Pons Ozelle is the 3rd and current Bishop of Nebbi Diocese, Uganda, now serving his fourth term. He holds a Master''s Degree in Theology from Trinity Theological University (Singapore), along with academic qualifications in theology, education, law, and business. Ordained in 2000, he has served as Diocesan Secretary, Administrator, Archdeacon, and parish priest. He worked with World Vision and taught in various institutions, serving on school boards. He is a strong advocate for education and youth empowerment, with particular passion for ministries for children, youth, women, and community development.',
     'img/team-bishop-pons.jpg', 4),
    ('Faith Mbabazi Musinguzi, PhD', 'Director',
     'Dr. Faith Mbabazi Musinguzi is an experienced educator, counselor, researcher, and academic leader with more than 20 years in higher education and mental wellness. She serves as Head of Postgraduate Studies (Faculty of Education) at Uganda Christian University (UCU) and previously led the Education Department. Dr. Mbabazi holds a PhD in Education with research focused on role conflict and burnout among university administrators. Her expertise includes educational psychology, guidance and counseling, learner mental well-being, higher-education leadership, and lifelong learning. She also leads the Agape Wellness Counseling Firm and serves as vice president of the Uganda Private Universities Lecturers'' Consortium.',
     'img/team-faith-mbabazi.jpg', 5),
    ('Alvis Asiimwe', 'Director',
     'Alvis Asiimwe is a dedicated childcare provider and aspiring nurse based in Ridgefield, Washington. She graduated in 2025 from Ridgefield High School through the Running Start program and continues nursing prerequisites at Clark College. She works as a hostess at IHOP and serves as a nursery attendant at Ridgefield United Methodist Church, where she provides safe, engaging care for young children, with additional experience in babysitting and early childhood supervision. She is deeply committed to social justice and community service, and hopes to combine her love for caregiving and advocacy through a future career in nursing.',
     'img/team-alvis-asiimwe.jpg', 6);

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
-- story_board_letter — inspirational quote requested in the client's 9/17/26 feedback
-- --------------------------------------------------
INSERT INTO story_board_letter (id, author_name, author_title, body) VALUES
    (1, 'Mahatma Gandhi', '',
     'The best way to find yourself is to lose yourself in the service of others.');

-- --------------------------------------------------
-- gallery_albums / gallery_images — real photos fetched from the live site's
-- actual "Our Year in Pictures" gallery (craft1-craft7 + crafts, same filenames
-- the live site itself uses), resized for web use.
-- --------------------------------------------------
INSERT INTO gallery_albums (id, name, slug, sort_order) VALUES
    (1, 'Our Year in Pictures', 'our-year-in-pictures', 1);

INSERT INTO gallery_images (album_id, image_path, caption, sort_order) VALUES
    (1, 'img/craft1.jpg', NULL, 1),
    (1, 'img/craft2.jpg', NULL, 2),
    (1, 'img/craft3.jpg', NULL, 3),
    (1, 'img/craft4.jpg', NULL, 4),
    (1, 'img/craft5.jpg', NULL, 5),
    (1, 'img/craft6.jpg', NULL, 6),
    (1, 'img/craft7.jpg', NULL, 7),
    (1, 'img/crafts.jpg', NULL, 8);

-- --------------------------------------------------
-- blog_posts — the 4 real posts currently live on bloombeyondborders.org/blog/
-- (bodies lightly expanded from the live site's single-sentence excerpts with
-- only already-verified organizational context, nothing fabricated)
-- --------------------------------------------------
INSERT INTO blog_posts (title, slug, body, featured_image_path, status, published_at) VALUES
    ('Skills training', 'skills-training',
     'Practical, hands-on training in vocational and business skills tailored to local market opportunities. Part of Bloom Beyond Borders'' work empowering women in Uganda with the tools to build sustainable income.',
     'img/craft3.jpg', 'published', '2026-04-13 09:00:00'),
    ('Entrepreneurship', 'entrepreneurship',
     'Guidance on starting and growing small businesses, from idea to income, with mentorship support. This initiative connects women with practical guidance to turn skills into lasting economic opportunity.',
     'img/craft6.jpg', 'published', '2026-04-13 09:00:00'),
    ('Community networks', 'community-networks',
     'Connecting women with peers and mentors to foster collaboration, solidarity, and shared growth. Strong community networks help women support each other through every stage of their journey.',
     'img/crafts.jpg', 'published', '2026-04-13 09:00:00'),
    ('Financial literacy', 'financial-literacy',
     'Understanding savings, budgeting, and credit to build long-term economic security for families. Financial literacy is a foundational skill that helps women and families plan for a more stable future.',
     'img/craft4.jpg', 'published', '2026-04-12 09:00:00');
