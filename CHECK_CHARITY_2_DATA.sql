-- Check data for charity_id = 2 (YOUR charity)

-- 1. Count campaigns
SELECT COUNT(*) as campaign_count
FROM campaigns
WHERE charity_id = 2 AND status != 'archived';

-- 2. Show campaigns with dates
SELECT 
    id, title, created_at, status, charity_id
FROM campaigns
WHERE charity_id = 2 AND status != 'archived'
ORDER BY created_at DESC;

-- 3. Count completed donations for your campaigns
SELECT COUNT(*) as donation_count, SUM(amount) as total
FROM donations d
JOIN campaigns c ON d.campaign_id = c.id
WHERE c.charity_id = 2 AND d.status = 'completed';

-- 4. Show donations with dates
SELECT 
    d.id, d.amount, d.status, d.created_at, c.title as campaign
FROM donations d
JOIN campaigns c ON d.campaign_id = c.id
WHERE c.charity_id = 2 AND d.status = 'completed'
ORDER BY d.created_at DESC;
