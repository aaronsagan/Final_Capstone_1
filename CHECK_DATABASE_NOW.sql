-- RUN THESE QUERIES IN YOUR DATABASE TO SEE WHAT DATA EXISTS

-- 1. Check total campaigns
SELECT COUNT(*) as total_campaigns FROM campaigns WHERE status != 'archived';

-- 2. Check campaigns by charity
SELECT charity_id, COUNT(*) as count 
FROM campaigns 
WHERE status != 'archived'
GROUP BY charity_id;

-- 3. Check campaigns in last 12 months
SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    COUNT(*) as count
FROM campaigns
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
  AND status != 'archived'
GROUP BY month
ORDER BY month;

-- 4. Check total completed donations
SELECT COUNT(*) as total_donations FROM donations WHERE status = 'completed';

-- 5. Check donations by campaign charity
SELECT 
    c.charity_id,
    COUNT(d.id) as donation_count,
    SUM(d.amount) as total_amount
FROM donations d
JOIN campaigns c ON d.campaign_id = c.id
WHERE d.status = 'completed'
GROUP BY c.charity_id;

-- 6. Check donations in last 12 months
SELECT 
    DATE_FORMAT(d.created_at, '%Y-%m') as month,
    COUNT(*) as count,
    SUM(d.amount) as total
FROM donations d
WHERE d.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
  AND d.status = 'completed'
GROUP BY month
ORDER BY month;

-- 7. Check your user's charity_id
SELECT id, name, email, charity_id FROM users WHERE email = 'YOUR_EMAIL_HERE';

-- 8. Check all campaigns with their charity
SELECT 
    c.id,
    c.title,
    c.charity_id,
    ch.name as charity_name,
    c.created_at,
    c.status
FROM campaigns c
LEFT JOIN charities ch ON c.charity_id = ch.id
WHERE c.status != 'archived'
ORDER BY c.created_at DESC
LIMIT 10;
