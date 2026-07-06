-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 06 juil. 2026 à 07:44
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `gestion_budgetaire`
--

-- --------------------------------------------------------

--
-- Structure de la table `budgets`
--

CREATE TABLE `budgets` (
  `id_budget` int(11) NOT NULL,
  `montant` decimal(15,2) DEFAULT NULL,
  `annee` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `budgets`
--

INSERT INTO `budgets` (`id_budget`, `montant`, `annee`) VALUES
(20, 9990000.00, '2026-2027');

-- --------------------------------------------------------

--
-- Structure de la table `departements`
--

CREATE TABLE `departements` (
  `id_departement` int(11) NOT NULL,
  `nom_departement` varchar(50) DEFAULT NULL,
  `annee` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `departements`
--

INSERT INTO `departements` (`id_departement`, `nom_departement`, `annee`) VALUES
(19, 'Etude', '2026-2027'),
(20, 'Administration', '2026-2027'),
(21, 'Logistique', '2026-2027');

-- --------------------------------------------------------

--
-- Structure de la table `depense_prevision`
--

CREATE TABLE `depense_prevision` (
  `id_depense_prevision` int(11) NOT NULL,
  `id_departement` int(11) DEFAULT NULL,
  `id_budget` int(11) DEFAULT NULL,
  `rubrique` varchar(50) DEFAULT NULL,
  `montant_departement` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `depense_prevision`
--

INSERT INTO `depense_prevision` (`id_depense_prevision`, `id_departement`, `id_budget`, `rubrique`, `montant_departement`) VALUES
(139, 19, 20, 'Formation', 450000.00),
(140, 20, 20, 'Logiciel', 400000.00),
(141, 21, 20, 'securité', 900000.00),
(142, 19, 20, 'Examen', 600000.00),
(143, 20, 20, 'salaire de prof', 150000.00),
(144, 21, 20, 'menage', 500000.00),
(145, 19, 20, 'Ecolage', 60000.00),
(146, 20, 20, 'transport', 450000.00),
(147, 21, 20, 'electricité', 800000.00);

-- --------------------------------------------------------

--
-- Structure de la table `depense_realisees`
--

CREATE TABLE `depense_realisees` (
  `id_depense_realisee` int(11) NOT NULL,
  `id_budget` int(11) DEFAULT NULL,
  `id_departement` int(11) DEFAULT NULL,
  `rubrique` varchar(50) DEFAULT NULL,
  `montant` decimal(15,2) DEFAULT NULL,
  `date_depense` date DEFAULT NULL,
  `annee` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `depense_realisees`
--

INSERT INTO `depense_realisees` (`id_depense_realisee`, `id_budget`, `id_departement`, `rubrique`, `montant`, `date_depense`, `annee`) VALUES
(71, 20, 19, 'Formation', 200000.00, '2026-06-19', '2026-2027'),
(72, 20, 20, 'Logiciel', 200000.00, '2026-06-19', '2026-2027'),
(73, 20, 21, 'Securité', 100000.00, '2026-06-19', '2026-2027'),
(74, 20, 19, 'Examen', 300000.00, '2026-06-19', '2026-2027'),
(75, 20, 20, 'Transport', 50000.00, '2026-06-19', '2026-2027'),
(76, 20, 21, 'Menage', 200000.00, '2026-06-19', '2026-2027'),
(77, 20, 19, 'Ecolage', 30000.00, '2026-06-19', '2026-2027'),
(78, 20, 20, 'salaire de prof', 400000.00, '2026-06-19', '2026-2027'),
(79, 20, 21, 'Electricite', 200000.00, '2026-06-19', '2026-2027');

-- --------------------------------------------------------

--
-- Structure de la table `imports_history`
--

CREATE TABLE `imports_history` (
  `id` int(11) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_hash` varchar(32) NOT NULL,
  `imported_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `imports_history`
--

INSERT INTO `imports_history` (`id`, `file_type`, `file_hash`, `imported_at`) VALUES
(23, 'budget', 'd13f78abd7614d26e5fbbb91eb22fa75', '2026-07-06 05:43:22'),
(24, 'depense', 'e152de8c67192f1537c1f40e9cc36500', '2026-07-06 05:43:40');

-- --------------------------------------------------------

--
-- Structure de la table `import_logs`
--

CREATE TABLE `import_logs` (
  `id_import` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `nom_fichier` varchar(50) DEFAULT NULL,
  `type_import` varchar(20) DEFAULT NULL,
  `date_import` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `import_logs`
--

INSERT INTO `import_logs` (`id_import`, `id_utilisateur`, `nom_fichier`, `type_import`, `date_import`) VALUES
(26, 4, 'budget.xlsx', 'Budget Annuel', '2026-07-06'),
(27, 4, 'depenses.xlsx', 'Depenses Realisees', '2026-07-06');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id_utilisateur` int(11) NOT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mot_de_passe` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id_utilisateur`, `nom`, `email`, `mot_de_passe`) VALUES
(4, 'Iavisoa Fitsikina', 'admin@gmail.com', '$2y$12$aiUPJ.uYD.QvMpLkR/Gn7epQ4eJazYNhRXFjXkjOun9CqRATlD1CG');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `budgets`
--
ALTER TABLE `budgets`
  ADD PRIMARY KEY (`id_budget`);

--
-- Index pour la table `departements`
--
ALTER TABLE `departements`
  ADD PRIMARY KEY (`id_departement`);

--
-- Index pour la table `depense_prevision`
--
ALTER TABLE `depense_prevision`
  ADD PRIMARY KEY (`id_depense_prevision`),
  ADD KEY `id_departement` (`id_departement`),
  ADD KEY `id_budget` (`id_budget`);

--
-- Index pour la table `depense_realisees`
--
ALTER TABLE `depense_realisees`
  ADD PRIMARY KEY (`id_depense_realisee`),
  ADD KEY `id_budget` (`id_budget`),
  ADD KEY `id_departement` (`id_departement`);

--
-- Index pour la table `imports_history`
--
ALTER TABLE `imports_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_file_hash_type` (`file_hash`,`file_type`);

--
-- Index pour la table `import_logs`
--
ALTER TABLE `import_logs`
  ADD PRIMARY KEY (`id_import`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id_utilisateur`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `budgets`
--
ALTER TABLE `budgets`
  MODIFY `id_budget` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `departements`
--
ALTER TABLE `departements`
  MODIFY `id_departement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT pour la table `depense_prevision`
--
ALTER TABLE `depense_prevision`
  MODIFY `id_depense_prevision` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT pour la table `depense_realisees`
--
ALTER TABLE `depense_realisees`
  MODIFY `id_depense_realisee` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT pour la table `imports_history`
--
ALTER TABLE `imports_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT pour la table `import_logs`
--
ALTER TABLE `import_logs`
  MODIFY `id_import` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id_utilisateur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `depense_prevision`
--
ALTER TABLE `depense_prevision`
  ADD CONSTRAINT `depense_prevision_ibfk_1` FOREIGN KEY (`id_departement`) REFERENCES `departements` (`id_departement`) ON DELETE CASCADE,
  ADD CONSTRAINT `depense_prevision_ibfk_2` FOREIGN KEY (`id_budget`) REFERENCES `budgets` (`id_budget`) ON DELETE CASCADE;

--
-- Contraintes pour la table `depense_realisees`
--
ALTER TABLE `depense_realisees`
  ADD CONSTRAINT `depense_realisees_ibfk_1` FOREIGN KEY (`id_budget`) REFERENCES `budgets` (`id_budget`) ON DELETE CASCADE,
  ADD CONSTRAINT `depense_realisees_ibfk_2` FOREIGN KEY (`id_departement`) REFERENCES `departements` (`id_departement`) ON DELETE CASCADE;

--
-- Contraintes pour la table `import_logs`
--
ALTER TABLE `import_logs`
  ADD CONSTRAINT `import_logs_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
