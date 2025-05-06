-- Vérifier si la colonne type_victime_id existe
SHOW COLUMNS FROM t_expertises LIKE 'type_victime_id';

-- Renommer la colonne type_victime_id en victime_de si elle existe
-- Sinon, ajouter une colonne victime_de qui pointera vers t_types_expertise
ALTER TABLE t_expertises 
DROP FOREIGN KEY IF EXISTS fk_type_victime,
CHANGE COLUMN IF EXISTS type_victime_id victime_de_id INT NULL;

-- Ajouter une clé étrangère vers t_types_expertise
ALTER TABLE t_expertises 
ADD CONSTRAINT fk_victime_de FOREIGN KEY (victime_de_id) REFERENCES t_types_expertise(id);

-- Si vous avez déjà des données dans la table, vous pouvez faire une migration
-- Pour chaque type de victime, associer le type d'expertise le plus approprié
-- Cela dépend de vos données spécifiques et nécessitera peut-être une décision manuelle 