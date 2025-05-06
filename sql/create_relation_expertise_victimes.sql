-- Créer la table de relation entre types d'expertise et types de victimes
CREATE TABLE IF NOT EXISTS t_types_expertise_victimes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type_expertise_id INT NOT NULL,
    type_victime_id INT NOT NULL,
    FOREIGN KEY (type_expertise_id) REFERENCES t_types_expertise(id),
    FOREIGN KEY (type_victime_id) REFERENCES t_types_victimes(id),
    UNIQUE (type_expertise_id, type_victime_id) -- Éviter les doublons
);

-- Insérer quelques associations par défaut (à adapter selon vos besoins)
-- Par exemple, pour le type d'expertise Traumatologique
INSERT INTO t_types_expertise_victimes (type_expertise_id, type_victime_id)
SELECT 
    (SELECT id FROM t_types_expertise WHERE nom_type = 'Traumatologique' LIMIT 1),
    (SELECT id FROM t_types_victimes WHERE nom_type_victime = 'Accident de la route' LIMIT 1)
WHERE EXISTS (SELECT 1 FROM t_types_expertise WHERE nom_type = 'Traumatologique')
  AND EXISTS (SELECT 1 FROM t_types_victimes WHERE nom_type_victime = 'Accident de la route');

INSERT INTO t_types_expertise_victimes (type_expertise_id, type_victime_id)
SELECT 
    (SELECT id FROM t_types_expertise WHERE nom_type = 'Traumatologique' LIMIT 1),
    (SELECT id FROM t_types_victimes WHERE nom_type_victime = 'Accident du travail' LIMIT 1)
WHERE EXISTS (SELECT 1 FROM t_types_expertise WHERE nom_type = 'Traumatologique')
  AND EXISTS (SELECT 1 FROM t_types_victimes WHERE nom_type_victime = 'Accident du travail');

-- Pour le type d'expertise Psychiatrique
INSERT INTO t_types_expertise_victimes (type_expertise_id, type_victime_id)
SELECT 
    (SELECT id FROM t_types_expertise WHERE nom_type = 'Psychiatrique' LIMIT 1),
    (SELECT id FROM t_types_victimes WHERE nom_type_victime = 'Agression' LIMIT 1)
WHERE EXISTS (SELECT 1 FROM t_types_expertise WHERE nom_type = 'Psychiatrique')
  AND EXISTS (SELECT 1 FROM t_types_victimes WHERE nom_type_victime = 'Agression');

INSERT INTO t_types_expertise_victimes (type_expertise_id, type_victime_id)
SELECT 
    (SELECT id FROM t_types_expertise WHERE nom_type = 'Psychiatrique' LIMIT 1),
    (SELECT id FROM t_types_victimes WHERE nom_type_victime = 'Violence conjugale' LIMIT 1)
WHERE EXISTS (SELECT 1 FROM t_types_expertise WHERE nom_type = 'Psychiatrique')
  AND EXISTS (SELECT 1 FROM t_types_victimes WHERE nom_type_victime = 'Violence conjugale');

INSERT INTO t_types_expertise_victimes (type_expertise_id, type_victime_id)
SELECT 
    (SELECT id FROM t_types_expertise WHERE nom_type = 'Psychiatrique' LIMIT 1),
    (SELECT id FROM t_types_victimes WHERE nom_type_victime = 'Maltraitance' LIMIT 1)
WHERE EXISTS (SELECT 1 FROM t_types_expertise WHERE nom_type = 'Psychiatrique')
  AND EXISTS (SELECT 1 FROM t_types_victimes WHERE nom_type_victime = 'Maltraitance'); 