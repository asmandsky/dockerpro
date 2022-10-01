
-- В базе данных имеется таблица с товарами goods (id INTEGER, name TEXT), таблица с тегами tags (id INTEGER, name TEXT) и таблица связи товаров и тегов goods_tags (tag_id INTEGER, goods_id INTEGER, UNIQUE(tag_id, goods_id)). Выведите id и названия всех товаров, которые имеют все возможные теги в этой базе.
SELECT ig.id, ig.name, igt1.count
FROM inetstudio_goods AS ig
         INNER JOIN (
    SELECT igt.goods_id,
           IF(count(igt.tag_id) = (SELECT COUNT(*) FROM inetstudio_tags),1,0) AS count
    FROM inetstudio_goods_tags AS igt
        INNER JOIN inetstudio_tags AS it ON it.id = igt.tag_id
    GROUP BY igt.goods_id
) AS igt1 ON igt1.goods_id = ig.id
WHERE igt1.count = 1

-- Выбрать без join-ов и подзапросов все департаменты, в которых есть мужчины, и все они (каждый) поставили высокую оценку (строго выше 5).
SELECT department_id
FROM inetstudio_evaluations
WHERE gender = 1
AND value > 5
GROUP BY department_id
