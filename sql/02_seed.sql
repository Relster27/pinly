-- Demo login: username "demo", password "demo1234"
INSERT INTO users (username, email, password_hash) VALUES
  ('demo', 'demo@example.com', '$2y$12$nvOUtgZNbiE7Wg4aY1hX/.HHWsPZl/Tw6xerlskSbdOKwHkwn.AFC');

-- 5 boards for the demo user, each with one cover pin below.
INSERT INTO boards (user_id, name)
SELECT id, b.name FROM users
CROSS JOIN (
  SELECT 'Weekend Trips' AS name UNION ALL
  SELECT 'Recipes' UNION ALL
  SELECT 'UI Inspiration' UNION ALL
  SELECT 'Apartment Ideas' UNION ALL
  SELECT 'DIY Projects'
) b
WHERE users.username = 'demo';

-- One cover pin per board, image served from the committed public/demo-content/ folder.
INSERT INTO pins (user_id, image_path, title, description)
SELECT id, p.image_path, p.title, 'Seeded demo content.' FROM users
CROSS JOIN (
  SELECT 'demo-content/board-weekend-trips.jpg' AS image_path, 'Weekend Trips cover' AS title UNION ALL
  SELECT 'demo-content/board-recipes.jpg', 'Recipes cover' UNION ALL
  SELECT 'demo-content/board-ui-inspiration.jpg', 'UI Inspiration cover' UNION ALL
  SELECT 'demo-content/board-apartment-ideas.jpg', 'Apartment Ideas cover' UNION ALL
  SELECT 'demo-content/board-diy-projects.jpg', 'DIY Projects cover'
) p
WHERE users.username = 'demo';

-- Attach each cover pin to its matching board (matched by name, not by id, so
-- insert order never matters).
INSERT INTO board_pins (board_id, pin_id)
SELECT b.id, p.id
FROM boards b
JOIN pins p ON p.title = CONCAT(b.name, ' cover')
JOIN users u ON u.id = b.user_id
WHERE u.username = 'demo';

-- 28 general feed pins (not attached to any board).
INSERT INTO pins (user_id, image_path, title, description)
SELECT id, f.image_path, f.title, 'Seeded demo content.' FROM users
CROSS JOIN (
  SELECT 'demo-content/pin-01.jpg' AS image_path, 'Minimal desk setup' AS title UNION ALL
  SELECT 'demo-content/pin-02.jpg', 'Weekend hike trail' UNION ALL
  SELECT 'demo-content/pin-03.jpg', 'Sourdough starter' UNION ALL
  SELECT 'demo-content/pin-04.jpg', 'Pastel UI kit' UNION ALL
  SELECT 'demo-content/pin-05.jpg', 'Terracotta planters' UNION ALL
  SELECT 'demo-content/pin-06.jpg', 'City at dusk' UNION ALL
  SELECT 'demo-content/pin-07.jpg', 'Hand-lettering practice' UNION ALL
  SELECT 'demo-content/pin-08.jpg', 'Studio lighting rig' UNION ALL
  SELECT 'demo-content/pin-09.jpg', 'Cold brew recipe' UNION ALL
  SELECT 'demo-content/pin-10.jpg', 'Mid-century chair' UNION ALL
  SELECT 'demo-content/pin-11.jpg', 'Botanical print set' UNION ALL
  SELECT 'demo-content/pin-12.jpg', 'Tiny balcony garden' UNION ALL
  SELECT 'demo-content/pin-13.jpg', 'Watercolor palette' UNION ALL
  SELECT 'demo-content/pin-14.jpg', 'Vintage camera collection' UNION ALL
  SELECT 'demo-content/pin-15.jpg', 'Linen color study' UNION ALL
  SELECT 'demo-content/pin-16.jpg', 'Night market stall' UNION ALL
  SELECT 'demo-content/pin-17.jpg', 'Minimal desk setup' UNION ALL
  SELECT 'demo-content/pin-18.jpg', 'Weekend hike trail' UNION ALL
  SELECT 'demo-content/pin-19.jpg', 'Sourdough starter' UNION ALL
  SELECT 'demo-content/pin-20.jpg', 'Pastel UI kit' UNION ALL
  SELECT 'demo-content/pin-21.jpg', 'Terracotta planters' UNION ALL
  SELECT 'demo-content/pin-22.jpg', 'City at dusk' UNION ALL
  SELECT 'demo-content/pin-23.jpg', 'Hand-lettering practice' UNION ALL
  SELECT 'demo-content/pin-24.jpg', 'Studio lighting rig' UNION ALL
  SELECT 'demo-content/pin-25.jpg', 'Cold brew recipe' UNION ALL
  SELECT 'demo-content/pin-26.jpg', 'Mid-century chair' UNION ALL
  SELECT 'demo-content/pin-27.jpg', 'Botanical print set' UNION ALL
  SELECT 'demo-content/pin-28.jpg', 'Tiny balcony garden'
) f
WHERE users.username = 'demo';
