// Connexion à la base MongoDB
const db = connect("mongodb://root:example@localhost:27017/AnalyseGeo");

// Suppression des collections si elles existent déjà
db.import_villes.drop();
db.villes.drop();
db.pays_continent.drop();
db.publications.drop();
db.revues.drop();
db.conferences.drop();
db.auteurs.drop();
db.a_ecrit.drop();

// Importation des villes depuis CSV
print("Importation des villes...");
db.villes.insertMany(
  JSON.parse(cat('/data/db/worldcities.json'))
);

// Transformation des données et insertion dans la collection `villes`
print("Transformation des villes...");
db.import_villes.find().forEach(function (doc) {
  db.villes.insertOne({
    _id: doc.id,
    nom_ville: doc.city_ascii,
    latitude: doc.lat,
    longitude: doc.lng,
    iso: doc.iso3,
    nom_pays: doc.country
  });
});

// Suppression de la table temporaire
db.import_villes.drop();

// Importation des pays et continents depuis CSV
print("Importation des pays et continents...");
db.pays_continent.insertMany(JSON.parse(
  cat("countries-continents.json"))
);

// Création de publications, revues et conférences
print("Importation des publications...");
db.publications.insertMany([
  { id_dblp: "pub1", type: "revue", titre: "Article A", annee: 2024 },
  { id_dblp: "pub2", type: "conference", titre: "Conférence B", annee: 2023 }
]);

db.revues.insertMany([
  { id_dblp: "pub1", volume: "12", numero: 3 }
]);

db.conferences.insertMany([
  { id_dblp: "pub2" }
]);

// Importation des auteurs
print("Importation des auteurs...");
db.auteurs.insertMany([
  { pid: "auth1", nom: "Dupont", prenom: "Jean" },
  { pid: "auth2", nom: "Smith", prenom: "Alice" }
]);

// Liens entre auteurs et publications
db.a_ecrit.insertMany([
  { id_dblp: "pub1", pid: "auth1", ordre: 1 },
  { id_dblp: "pub2", pid: "auth2", ordre: 1 }
]);

print("Importation terminée !");
