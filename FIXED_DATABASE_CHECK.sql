-- FIXED: Run this to check your data

-- 1. Check your user and charity info
SELECT 
    u.id as user_id,
    u.email,
    u.role,
    c.id as charity_id,
    c.name as charity_name
FROM users u
LEFT JOIN charities c ON u.id = c.user_id
ORDER BY u.created_at DESC
LIMIT 1;

-- 2. Total campaigns
SELECT COUNT(*) as total_campaigns 
FROM campaigns 
WHERE status != 'archived';

-- 3. Campaigns by charity  
SELECT 
    charity_id, 
    COUNT(*) as campaign_count 
FROM campaigns 
WHERE status != 'archived'
GROUP BY charity_id;

-- 4. Campaigns in last 12 months
SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    COUNT(*) as count
FROM campaigns
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
  AND status != 'archived'
GROUP BY month
ORDER BY month;

-- 5. Total completed donations
SELECT COUNT(*) as total_donations 
FROM donations 
WHERE status = 'completed';

-- 6. Completed donations in last 12 months
SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    COUNT(*) as count,
    SUM(amount) as total_amount
FROM donations
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
  AND status = 'completed'
GROUP BY month
ORDER BY month;

-- 7. Show me your actual charity and its campaigns
SELECT 
    c.id as charity_id,
    c.name as charity_name,
    (SELECT COUNT(*) FROM campaigns WHERE charity_id = c.id AND status != 'archived') as campaign_count,
    (SELECT COUNT(*) 
     FROM donations d 
     JOIN campaigns camp ON d.campaign_id = camp.id 
     WHERE camp.charity_id = c.id AND d.status = 'completed') as donation_count
FROM charities c
WHERE c.user_id = (SELECT id FROM users WHERE role = 'charity' ORDER BY created_at DESC LIMIT 1);
