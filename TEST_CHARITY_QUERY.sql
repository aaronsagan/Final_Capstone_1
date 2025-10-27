-- Run this to check YOUR specific user (ID: 2)

-- 1. Check your user and charity
SELECT 
    u.id as user_id,
    u.name,
    u.email,
    u.role,
    c.id as charity_id,
    c.name as charity_name
FROM users u
LEFT JOIN charities c ON c.owner_id = u.id
WHERE u.id = 2;

-- 2. Check campaigns for your charity
SELECT 
    c.id,
    c.title,
    c.charity_id,
    c.created_at,
    c.status
FROM campaigns c
WHERE c.charity_id = (SELECT id FROM charities WHERE owner_id = 2)
ORDER BY c.created_at DESC;

-- 3. Check completed donations
SELECT 
    d.id,
    d.campaign_id,
    d.amount,
    d.status,
    d.created_at,
    ca.title as campaign_title
FROM donations d
JOIN campaigns ca ON d.campaign_id = ca.id
WHERE ca.charity_id = (SELECT id FROM charities WHERE owner_id = 2)
  AND d.status = 'completed'
ORDER BY d.created_at DESC;
