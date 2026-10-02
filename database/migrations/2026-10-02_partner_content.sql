-- Partner write-ups from the client's Partners page feedback.
-- Requires 2026-10-02_partner_details.sql. Skips any partner whose name already exists,
-- so it is safe to re-run. Logos are added afterwards via Admin -> Partners.
INSERT INTO partners (name, tagline, description, sort_order)
SELECT 'Nebbi Diocese', 'Growing Hope and Opportunity Together',
'At Bloom Beyond Borders, we believe meaningful change begins with partnership: listening, learning, and coming alongside people who know their communities best. That is why we are honored to partner with the Church of Uganda, Nebbi Diocese, in northern Uganda.

Nebbi Diocese has deep roots in the communities it serves. Together, we support its work in three areas that shape everyday life.

## Women''s Empowerment
Through skilling initiatives, women learn, grow, and strengthen their livelihoods. These are pathways to confidence, dignity, and independence, and when a woman is given an opportunity, the impact can reach an entire family.

## Education
We support initiatives that help children and young people access opportunities to learn and grow. For a child, education can open a door to possibilities that once seemed out of reach. We want to help keep those doors open.

## Healthcare
Good health is the foundation on which families thrive. By strengthening access to care and backing local efforts to promote wellbeing, we help children grow, women flourish, and families look to the future with hope.

## A Partnership Rooted in Trust
We believe in locally led solutions. Our role is not to do the work for a community. It is to come alongside, listen, support, and help good work grow.

## Help us help more women and children bloom.
Your gift makes partnerships like this possible. Together with Nebbi Diocese, we are helping women grow, children learn, families thrive, and communities bloom.', 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM partners WHERE name = 'Nebbi Diocese');

INSERT INTO partners (name, tagline, description, sort_order)
SELECT 'Agape Counselling and Consultancy Services', 'Supporting Mental Health and Emotional Wellbeing',
'At Bloom Beyond Borders, we believe caring for people means caring for the whole person, including their emotional and mental wellbeing. We are honored to partner with Agape Counselling and Consultancy Services in Mukono, Uganda, whose counselling and mental health support is deeply relevant to the communities we serve.

Women, children, young people, and adults can all face challenges that affect their emotional wellbeing. A safe, supportive place to talk, be heard, and receive professional guidance can make an important difference.

## Creating Safe Spaces to Heal and Grow
No one should have to navigate difficult seasons alone. Together with Agape, we support individuals as they work through challenges, build resilience, strengthen relationships, and move toward greater emotional wellbeing.

## A Partnership Rooted in Care
Emotional wellbeing is an essential part of healthy families and thriving communities. When people are supported from the inside out, families and communities have greater opportunity to flourish.

## Help us help more women and children bloom.
Your gift makes partnerships like this possible. Together with Agape, we are helping people heal, families grow stronger, and communities bloom.', 2
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM partners WHERE name = 'Agape Counselling and Consultancy Services');

INSERT INTO partners (name, tagline, description, sort_order)
SELECT 'AFRHEEC', 'Building Community, Belonging, and Opportunity',
'At Bloom Beyond Borders, we believe everyone deserves to feel supported, connected, and empowered to thrive in their new home. We are honored to partner with AFRHEEC in Oregon in supporting African immigrants and families as they navigate life in the United States.

For many African immigrants, building a new life brings both opportunity and challenge: navigating unfamiliar systems, accessing resources, finding community, and creating pathways toward stability and independence.

## Growing Together
Through this partnership, we come alongside African immigrant communities with a shared commitment to empowerment, education, connection, and family wellbeing. We are proud to support work that honors the strengths, talents, cultures, and aspirations African immigrants bring to their communities.

## A Partnership Rooted in Belonging
Strong communities are built when people have the support and connections they need to flourish. Together, we are building bridges, strengthening families, and creating spaces where African immigrants can belong, contribute, and bloom.

## Help us help more women and children bloom.
Your gift makes partnerships like this possible. Together with AFRHEEC, we are helping families connect, communities grow stronger, and people bloom.', 3
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM partners WHERE name = 'AFRHEEC');
