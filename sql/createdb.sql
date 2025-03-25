-- Création du schéma et sélection du schéma
CREATE SCHEMA IF NOT EXISTS analysegeo;

--------------------------------------------------
-- Table import_villes (temporaire)
--------------------------------------------------
CREATE TABLE IF NOT EXISTS analysegeo.import_villes (
    city         VARCHAR(50),
    city_ascii   VARCHAR(50),
    lat          FLOAT,
    lng          FLOAT,
    country      VARCHAR(50),
    iso2         CHAR(2),
    iso3         CHAR(3),
    admin_name   VARCHAR(255),
    capital      VARCHAR(50),
    population   VARCHAR(255),
    id           INTEGER PRIMARY KEY
);
-- DROP TABLE analysegeo.import_villes;

--------------------------------------------------
-- Table _villes
--------------------------------------------------
CREATE TABLE IF NOT EXISTS analysegeo._villes (
    id         INTEGER NOT NULL PRIMARY KEY,
    nom_ville  VARCHAR(50) NOT NULL,
    latitude   FLOAT NOT NULL,
    longitude  FLOAT NOT NULL,
    iso        CHAR(4) NOT NULL,
    nom_pays   VARCHAR(50) NOT NULL
);
-- DROP TABLE analysegeo._villes;

-- Créer une séquence si elle n'existe pas déjà
CREATE SEQUENCE IF NOT EXISTS analysegeo._villes_id_seq;

-- Mettre à jour la séquence avec la valeur maximale existante
SELECT setval('analysegeo._villes_id_seq', (SELECT MAX(id) FROM analysegeo._villes));

-- Modifier la colonne "id" pour utiliser la séquence
ALTER TABLE analysegeo._villes
ALTER COLUMN id SET DEFAULT nextval('analysegeo._villes_id_seq');

--------------------------------------------------
-- Table pays_continent
--------------------------------------------------
CREATE TABLE IF NOT EXISTS analysegeo.pays_continent (
  continent  VARCHAR(15),
  pays       VARCHAR(50)
);
-- DROP TABLE analysegeo.pays_continent;

--------------------------------------------------
-- Chargement et insertion des données
--------------------------------------------------
COPY analysegeo.import_villes
FROM '/docker-entrypoint-initdb.d/worldcities.csv'
WITH (FORMAT csv, HEADER true, DELIMITER ',');

INSERT INTO analysegeo._villes (id, nom_ville, latitude, longitude, iso, nom_pays)
SELECT DISTINCT id, city_ascii, lat, lng, iso3, country
FROM analysegeo.import_villes
ON CONFLICT (id) DO NOTHING;

COPY analysegeo.pays_continent (continent, pays)
FROM '/docker-entrypoint-initdb.d/Countries-Continents.csv'
WITH (DELIMITER ',', FORMAT csv, HEADER true);

DROP TABLE analysegeo.import_villes;

--------------------------------------------------
-- Vue pour toutes les infos géographiques
--------------------------------------------------
CREATE OR REPLACE VIEW analysegeo.touteslesinfos AS (
  SELECT * FROM analysegeo._villes
  INNER JOIN analysegeo.pays_continent ON pays = nom_pays
);

--------------------------------------------------
-- Table _publications
--------------------------------------------------
CREATE TABLE IF NOT EXISTS analysegeo._publications (
  id_dblp  VARCHAR(255),
  type     VARCHAR(255),
  doi      VARCHAR(255),
  titre    VARCHAR(255),
  lieu     VARCHAR(255),
  annee    INTEGER,
  pages    VARCHAR(50),
  ee       VARCHAR(255),
  url_dblp VARCHAR(255)
);
ALTER TABLE analysegeo._publications ADD PRIMARY KEY (id_dblp);
-- DROP TABLE analysegeo._publications;

--------------------------------------------------
-- Table _revues (héritée de _publications)
--------------------------------------------------
CREATE TABLE IF NOT EXISTS analysegeo._revues (
  volume  VARCHAR(255),
  numero  INTEGER
) INHERITS (analysegeo._publications);
ALTER TABLE analysegeo._revues ADD PRIMARY KEY (id_dblp);
-- DROP TABLE analysegeo._revues;

--------------------------------------------------
-- Table _conferences (héritée de _publications)
--------------------------------------------------
CREATE TABLE IF NOT EXISTS analysegeo._conferences (
  -- données à intégrer ultérieurement
) INHERITS (analysegeo._publications);
ALTER TABLE analysegeo._conferences ADD PRIMARY KEY (id_dblp);
-- DROP TABLE analysegeo._conferences;

--------------------------------------------------
-- Table _auteurs
--------------------------------------------------
CREATE TABLE IF NOT EXISTS analysegeo._auteurs (
  pid     VARCHAR(255),
  orc_id  VARCHAR(255),
  nom     VARCHAR(255)
);
ALTER TABLE analysegeo._auteurs ADD PRIMARY KEY (pid);
-- DROP TABLE analysegeo._auteurs;

--------------------------------------------------
-- Table a_ecrit : relation entre publications et auteurs
--------------------------------------------------
CREATE TABLE IF NOT EXISTS analysegeo.a_ecrit (
  id_dblp  VARCHAR(255),
  pid      VARCHAR(255),
  ordre    INT
);
ALTER TABLE analysegeo.a_ecrit ADD PRIMARY KEY (id_dblp, pid);
ALTER TABLE analysegeo.a_ecrit ADD FOREIGN KEY (id_dblp) REFERENCES analysegeo._publications(id_dblp);
ALTER TABLE analysegeo.a_ecrit ADD FOREIGN KEY (pid) REFERENCES analysegeo._auteurs(pid);
-- DROP TABLE analysegeo.a_ecrit;

--------------------------------------------------
-- Table _structures
--------------------------------------------------
CREATE TABLE IF NOT EXISTS analysegeo._structures (
  id_struct   VARCHAR(50),
  ror         VARCHAR(255),
  acronyme    VARCHAR(50),
  nom_struct  VARCHAR(255),
  id_ville    INTEGER
);
ALTER TABLE analysegeo._structures ADD PRIMARY KEY (id_struct);
ALTER TABLE analysegeo._structures ADD CONSTRAINT fk_ville FOREIGN KEY (id_ville) REFERENCES analysegeo._villes(id);
-- DROP TABLE analysegeo._structures;

--------------------------------------------------
-- Table _affiliation : relation entre auteur et structure
--------------------------------------------------
CREATE TABLE IF NOT EXISTS analysegeo._affiliation (
    pid       VARCHAR(255),
    id_struct VARCHAR(50)  
);
ALTER TABLE analysegeo._affiliation ADD PRIMARY KEY (pid, id_struct);
ALTER TABLE analysegeo._affiliation ADD FOREIGN KEY (pid) REFERENCES analysegeo._auteurs(pid) ON DELETE CASCADE;
ALTER TABLE analysegeo._affiliation ADD FOREIGN KEY (id_struct) REFERENCES analysegeo._structures(id_struct) ON DELETE CASCADE;
-- DROP TABLE analysegeo._affiliation;