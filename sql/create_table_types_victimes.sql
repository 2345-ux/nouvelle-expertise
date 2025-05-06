-- Créer la table des types de victimes
CREATE TABLE IF NOT EXISTS t_types_victimes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom_type_victime VARCHAR(100) NOT NULL,
    description TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Ajouter des types de victimes par défaut
INSERT INTO t_types_victimes (nom_type_victime, description) VALUES
('Accident de la route', 'Victimes d\'accidents sur la voie publique'),
('Agression', 'Victimes d\'agressions physiques'),
('Accident domestique', 'Accidents au domicile'),
('Accident du travail', 'Accidents en milieu professionnel'),
('Violence conjugale', 'Violences entre conjoints'),
('Maltraitance', 'Maltraitance sur personne vulnérable');

-- Ajouter une colonne à la table t_expertises si elle n'existe pas déjà
ALTER TABLE t_expertises 
ADD COLUMN IF NOT EXISTS type_victime_id INT,
ADD CONSTRAINT fk_type_victime FOREIGN KEY (type_victime_id) REFERENCES t_types_victimes(id);

-- Mise à jour des anciens enregistrements (si nécessaire)
-- Cette partie est à exécuter manuellement selon les besoins
-- UPDATE t_expertises SET type_victime_id = (SELECT id FROM t_types_victimes WHERE nom_type_victime = 'Accident de la route') WHERE victime_de LIKE '%accident%route%';
-- UPDATE t_expertises SET type_victime_id = (SELECT id FROM t_types_victimes WHERE nom_type_victime = 'Agression') WHERE victime_de LIKE '%agress%';

-- Suppression de l'ancienne colonne (à faire uniquement après la migration des données)
-- ALTER TABLE t_expertises DROP COLUMN victime_de; 