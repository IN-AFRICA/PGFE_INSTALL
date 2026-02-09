/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `InputAccount`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `InputAccount` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` double NOT NULL,
  `justification` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `account_plan_id` bigint unsigned NOT NULL,
  `sub_account_plan_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `school_year_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inputaccount_school_year_id_foreign` (`school_year_id`),
  CONSTRAINT `inputaccount_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `OutputAccount`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `OutputAccount` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` double NOT NULL,
  `justification` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `account_plan_id` bigint unsigned NOT NULL,
  `sub_account_plan_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `school_year_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `outputaccount_school_year_id_foreign` (`school_year_id`),
  CONSTRAINT `outputaccount_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `a_new_comptability`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `a_new_comptability` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `account_plan_id` bigint unsigned NOT NULL,
  `sub_account_plan_id` bigint unsigned NOT NULL,
  `amount` double NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `justification` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `abandon_cases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `abandon_cases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_year_id` bigint unsigned NOT NULL,
  `classroom_id` bigint unsigned NOT NULL,
  `filiere_id` bigint unsigned DEFAULT NULL,
  `student_id` bigint unsigned NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `semester_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `abandon_cases_school_year_id_foreign` (`school_year_id`),
  KEY `abandon_cases_classroom_id_foreign` (`classroom_id`),
  KEY `abandon_cases_filiere_id_foreign` (`filiere_id`),
  KEY `abandon_cases_student_id_foreign` (`student_id`),
  KEY `abandon_cases_semester_id_foreign` (`semester_id`),
  CONSTRAINT `abandon_cases_classroom_id_foreign` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `abandon_cases_filiere_id_foreign` FOREIGN KEY (`filiere_id`) REFERENCES `filiaires` (`id`) ON DELETE SET NULL,
  CONSTRAINT `abandon_cases_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `abandon_cases_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE SET NULL,
  CONSTRAINT `abandon_cases_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `academic_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `academic_levels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cycle_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `academic_levels_cycle_id_foreign` (`cycle_id`),
  CONSTRAINT `academic_levels_cycle_id_foreign` FOREIGN KEY (`cycle_id`) REFERENCES `cycles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `academic_personal_course`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `academic_personal_course` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `course_id` bigint unsigned NOT NULL,
  `academic_personal_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `academic_personal_course_course_id_foreign` (`course_id`),
  KEY `academic_personal_course_academic_personal_id_foreign` (`academic_personal_id`),
  CONSTRAINT `academic_personal_course_academic_personal_id_foreign` FOREIGN KEY (`academic_personal_id`) REFERENCES `academic_personals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `academic_personal_course_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `academic_personals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `academic_personals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `mechanisation_id` bigint unsigned DEFAULT NULL,
  `country_id` bigint unsigned NOT NULL,
  `province_id` bigint unsigned NOT NULL,
  `territory_id` bigint unsigned NOT NULL,
  `commune_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `type_id` bigint unsigned NOT NULL,
  `father_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `academic_level_id` bigint unsigned NOT NULL,
  `fonction_id` bigint unsigned NOT NULL,
  `matricule` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pre_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `identity_card_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `civil_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `physical_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `birth_date` date NOT NULL,
  `birth_place` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `academic_personals_matricule_unique` (`matricule`),
  UNIQUE KEY `academic_personals_phone_number_unique` (`phone`),
  UNIQUE KEY `academic_personals_email_unique` (`email`),
  UNIQUE KEY `academic_personals_identity_card_unique` (`identity_card_number`),
  KEY `academic_personals_user_id_foreign` (`user_id`),
  KEY `academic_personals_country_id_foreign` (`country_id`),
  KEY `academic_personals_province_id_foreign` (`province_id`),
  KEY `academic_personals_territory_id_foreign` (`territory_id`),
  KEY `academic_personals_commune_id_foreign` (`commune_id`),
  KEY `academic_personals_school_id_foreign` (`school_id`),
  KEY `academic_personals_type_id_foreign` (`type_id`),
  KEY `academic_personals_academic_level_id_foreign` (`academic_level_id`),
  KEY `academic_personals_fonction_id_foreign` (`fonction_id`),
  CONSTRAINT `academic_personals_academic_level_id_foreign` FOREIGN KEY (`academic_level_id`) REFERENCES `academic_levels` (`id`),
  CONSTRAINT `academic_personals_commune_id_foreign` FOREIGN KEY (`commune_id`) REFERENCES `communes` (`id`),
  CONSTRAINT `academic_personals_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`),
  CONSTRAINT `academic_personals_fonction_id_foreign` FOREIGN KEY (`fonction_id`) REFERENCES `fonctions` (`id`),
  CONSTRAINT `academic_personals_province_id_foreign` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`),
  CONSTRAINT `academic_personals_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `academic_personals_territory_id_foreign` FOREIGN KEY (`territory_id`) REFERENCES `territories` (`id`),
  CONSTRAINT `academic_personals_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `types` (`id`),
  CONSTRAINT `academic_personals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `account_plan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `account_plan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class_comptability_id` bigint unsigned NOT NULL,
  `category_comptability_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `school_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_plan_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `solde` double NOT NULL DEFAULT '0',
  `user_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounts_number_unique` (`number`),
  UNIQUE KEY `accounts_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `causer_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint unsigned DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `batch_uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ammortissement_comptabilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ammortissement_comptabilities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `justification` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_ammortissement` date NOT NULL,
  `amount` double NOT NULL,
  `immo_account_id` bigint unsigned NOT NULL,
  `immo_sub_account_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `annalytique_comptability_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `analytic_plan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `analytic_plan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `annalytique_comptabilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `annalytique_comptabilities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `annalytique_comptabilities_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `applications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` bigint unsigned NOT NULL,
  `job_offer_id` bigint unsigned NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `school_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `applications_candidate_id_foreign` (`candidate_id`),
  KEY `applications_job_offer_id_foreign` (`job_offer_id`),
  CONSTRAINT `applications_candidate_id_foreign` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`),
  CONSTRAINT `applications_job_offer_id_foreign` FOREIGN KEY (`job_offer_id`) REFERENCES `job_offers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `budget_comptability`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budget_comptability` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `candidates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `candidates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `school_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `candidates_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `category_comptability`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `category_comptability` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `school_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `class_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `class_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `school_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `class_accounts_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `class_comptability`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `class_comptability` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `school_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `classrooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `classrooms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `academic_level_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `indicator` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `titulaire_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `classrooms_academic_level_id_foreign` (`academic_level_id`),
  KEY `classrooms_titulaire_id_foreign` (`titulaire_id`),
  CONSTRAINT `classrooms_academic_level_id_foreign` FOREIGN KEY (`academic_level_id`) REFERENCES `academic_levels` (`id`) ON DELETE SET NULL,
  CONSTRAINT `classrooms_titulaire_id_foreign` FOREIGN KEY (`titulaire_id`) REFERENCES `academic_personals` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clients_school_id_foreign` (`school_id`),
  KEY `clients_user_id_foreign` (`user_id`),
  CONSTRAINT `clients_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `clients_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `communes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `communes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `province_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `communes_province_id_name_unique` (`province_id`,`name`),
  CONSTRAINT `communes_province_id_foreign` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `companies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `conduite_grades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conduite_grades` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_year_id` bigint unsigned NOT NULL,
  `filiere_id` bigint unsigned DEFAULT NULL,
  `classroom_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `fault_count` int NOT NULL,
  `conduite_semester_1_id` bigint unsigned DEFAULT NULL,
  `conduite_semester_2_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `conduite_grades_school_year_id_foreign` (`school_year_id`),
  KEY `conduite_grades_filiere_id_foreign` (`filiere_id`),
  KEY `conduite_grades_classroom_id_foreign` (`classroom_id`),
  KEY `conduite_grades_student_id_foreign` (`student_id`),
  KEY `conduite_grades_conduite_semester_1_id_foreign` (`conduite_semester_1_id`),
  KEY `conduite_grades_conduite_semester_2_id_foreign` (`conduite_semester_2_id`),
  CONSTRAINT `conduite_grades_classroom_id_foreign` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conduite_grades_conduite_semester_1_id_foreign` FOREIGN KEY (`conduite_semester_1_id`) REFERENCES `conduite_semesters` (`id`) ON DELETE SET NULL,
  CONSTRAINT `conduite_grades_conduite_semester_2_id_foreign` FOREIGN KEY (`conduite_semester_2_id`) REFERENCES `conduite_semesters` (`id`) ON DELETE SET NULL,
  CONSTRAINT `conduite_grades_filiere_id_foreign` FOREIGN KEY (`filiere_id`) REFERENCES `filiaires` (`id`) ON DELETE SET NULL,
  CONSTRAINT `conduite_grades_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conduite_grades_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `conduite_semesters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conduite_semesters` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_year_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `semester_id` bigint unsigned DEFAULT NULL,
  `conduite_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `conduite_semesters_school_year_id_foreign` (`school_year_id`),
  KEY `conduite_semesters_semester_id_foreign` (`semester_id`),
  KEY `conduite_semesters_conduite_id_foreign` (`conduite_id`),
  CONSTRAINT `conduite_semesters_conduite_id_foreign` FOREIGN KEY (`conduite_id`) REFERENCES `conduites` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conduite_semesters_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conduite_semesters_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `conduites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conduites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `conduites_school_id_foreign` (`school_id`),
  CONSTRAINT `conduites_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `countries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `courses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `academic_level_id` bigint unsigned NOT NULL,
  `filiaire_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `cycle_id` bigint unsigned DEFAULT NULL,
  `classroom_id` bigint unsigned NOT NULL,
  `hourly_volume` int NOT NULL,
  `max_period_1` double NOT NULL,
  `max_period_2` double NOT NULL,
  `max_period_3` double NOT NULL,
  `max_period_4` double NOT NULL,
  `max_exam_1` double NOT NULL,
  `max_exam_2` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `courses_level_id_foreign` (`academic_level_id`),
  KEY `courses_filiere_id_foreign` (`filiaire_id`),
  KEY `courses_school_id_foreign` (`school_id`),
  KEY `courses_classroom_id_foreign` (`classroom_id`),
  CONSTRAINT `courses_classroom_id_foreign` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `courses_filiere_id_foreign` FOREIGN KEY (`filiaire_id`) REFERENCES `filiaires` (`id`) ON DELETE CASCADE,
  CONSTRAINT `courses_level_id_foreign` FOREIGN KEY (`academic_level_id`) REFERENCES `academic_levels` (`id`) ON DELETE CASCADE,
  CONSTRAINT `courses_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `credits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `credits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `justification` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_plan_id` bigint unsigned DEFAULT NULL,
  `sub_account_plan_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `school_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `currencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `currencies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbol` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `currencies_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cycles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cycles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `filiaire_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cycles_filiaire_id_foreign` (`filiaire_id`),
  CONSTRAINT `cycles_filiaire_id_foreign` FOREIGN KEY (`filiaire_id`) REFERENCES `filiaires` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `debits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `debits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `justification` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_plan_id` bigint unsigned DEFAULT NULL,
  `sub_account_plan_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `school_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `deliberations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deliberations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `classroom_id` bigint unsigned NOT NULL,
  `filiaire_id` bigint unsigned NOT NULL,
  `academic_level_id` bigint unsigned NOT NULL,
  `cycle_id` bigint unsigned NOT NULL,
  `school_year_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `course_id` bigint unsigned NOT NULL,
  `is_validated` tinyint(1) NOT NULL DEFAULT '0',
  `conduite_grade_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deliberations_student_id_foreign` (`student_id`),
  KEY `deliberations_classroom_id_foreign` (`classroom_id`),
  KEY `deliberations_filiaire_id_foreign` (`filiaire_id`),
  KEY `deliberations_academic_level_id_foreign` (`academic_level_id`),
  KEY `deliberations_cycle_id_foreign` (`cycle_id`),
  KEY `deliberations_school_id_foreign` (`school_id`),
  KEY `deliberations_course_id_foreign` (`course_id`),
  KEY `deliberations_conduite_grade_id_foreign` (`conduite_grade_id`),
  KEY `deliberations_school_year_id_classroom_id_course_id_index` (`school_year_id`,`classroom_id`,`course_id`),
  CONSTRAINT `deliberations_academic_level_id_foreign` FOREIGN KEY (`academic_level_id`) REFERENCES `academic_levels` (`id`) ON DELETE CASCADE,
  CONSTRAINT `deliberations_classroom_id_foreign` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `deliberations_conduite_grade_id_foreign` FOREIGN KEY (`conduite_grade_id`) REFERENCES `conduite_grades` (`id`) ON DELETE SET NULL,
  CONSTRAINT `deliberations_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `deliberations_cycle_id_foreign` FOREIGN KEY (`cycle_id`) REFERENCES `cycles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `deliberations_filiaire_id_foreign` FOREIGN KEY (`filiaire_id`) REFERENCES `filiaires` (`id`) ON DELETE CASCADE,
  CONSTRAINT `deliberations_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `deliberations_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `deliberations_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `disciplinary_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `disciplinary_actions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `type_id` bigint unsigned NOT NULL,
  `author_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `disciplinary_actions_student_id_foreign` (`student_id`),
  KEY `disciplinary_actions_school_id_foreign` (`school_id`),
  KEY `disciplinary_actions_type_id_foreign` (`type_id`),
  KEY `disciplinary_actions_author_id_foreign` (`author_id`),
  CONSTRAINT `disciplinary_actions_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `academic_personals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `disciplinary_actions_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `disciplinary_actions_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `disciplinary_actions_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `disciplines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `disciplines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `type_id` bigint unsigned NOT NULL,
  `academic_personal_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `observation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discipline_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `disciplines_student_id_foreign` (`student_id`),
  KEY `disciplines_school_id_foreign` (`school_id`),
  KEY `disciplines_type_id_foreign` (`type_id`),
  KEY `disciplines_academic_personal_id_foreign` (`academic_personal_id`),
  CONSTRAINT `disciplines_academic_personal_id_foreign` FOREIGN KEY (`academic_personal_id`) REFERENCES `academic_personals` (`id`),
  CONSTRAINT `disciplines_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `disciplines_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  CONSTRAINT `disciplines_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `type_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `academic_personal_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `documents_student_id_foreign` (`student_id`),
  KEY `documents_type_id_foreign` (`type_id`),
  KEY `documents_academic_personal_id_foreign` (`academic_personal_id`),
  CONSTRAINT `documents_academic_personal_id_foreign` FOREIGN KEY (`academic_personal_id`) REFERENCES `academic_personals` (`id`),
  CONSTRAINT `documents_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `documents_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `equipments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `equipments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `quantity` int NOT NULL,
  `daily_price` decimal(10,2) NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `equipments_school_id_foreign` (`school_id`),
  KEY `equipments_user_id_foreign` (`user_id`),
  CONSTRAINT `equipments_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `equipments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `exchange_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exchange_rates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `currency_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `rate` decimal(15,6) NOT NULL,
  `date_effective` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_active_rate_per_currency` (`currency_id`,`is_active`),
  KEY `exchange_rates_school_id_foreign` (`school_id`),
  CONSTRAINT `exchange_rates_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exchange_rates_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `exercices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exercices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `payment_method_id` bigint unsigned NOT NULL,
  `account_type_id` bigint unsigned NOT NULL,
  `currency_id` bigint unsigned NOT NULL,
  `exchange_rate_id` bigint unsigned NOT NULL,
  `beneficiary` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expense_raison` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` double NOT NULL,
  `amount_converted` double DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `expense_date` date NOT NULL DEFAULT (curdate()),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `expenses_reference_unique` (`reference`),
  KEY `expenses_school_id_foreign` (`school_id`),
  KEY `expenses_user_id_foreign` (`user_id`),
  KEY `expenses_payment_method_id_foreign` (`payment_method_id`),
  KEY `expenses_account_type_id_foreign` (`account_type_id`),
  KEY `expenses_currency_id_foreign` (`currency_id`),
  KEY `expenses_exchange_rate_id_foreign` (`exchange_rate_id`),
  CONSTRAINT `expenses_account_type_id_foreign` FOREIGN KEY (`account_type_id`) REFERENCES `account_types` (`id`),
  CONSTRAINT `expenses_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  CONSTRAINT `expenses_exchange_rate_id_foreign` FOREIGN KEY (`exchange_rate_id`) REFERENCES `exchange_rates` (`id`),
  CONSTRAINT `expenses_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`),
  CONSTRAINT `expenses_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expenses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fee_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fee_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `fee_types_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `amount` double NOT NULL,
  `currency_id` bigint unsigned NOT NULL,
  `exchange_rate_id` bigint unsigned DEFAULT NULL,
  `fee_type_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `effective_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fees_currency_id_foreign` (`currency_id`),
  KEY `fees_fee_type_id_foreign` (`fee_type_id`),
  KEY `fees_school_id_foreign` (`school_id`),
  KEY `fees_exchange_rate_id_foreign` (`exchange_rate_id`),
  CONSTRAINT `fees_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  CONSTRAINT `fees_exchange_rate_id_foreign` FOREIGN KEY (`exchange_rate_id`) REFERENCES `exchange_rates` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fees_fee_type_id_foreign` FOREIGN KEY (`fee_type_id`) REFERENCES `fee_types` (`id`),
  CONSTRAINT `fees_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fiche_cotations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fiche_cotations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_year_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `classroom_id` bigint unsigned NOT NULL,
  `course_id` bigint unsigned NOT NULL,
  `note` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deliberation_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fiche_cotations_school_year_id_foreign` (`school_year_id`),
  KEY `fiche_cotations_student_id_foreign` (`student_id`),
  KEY `fiche_cotations_classroom_id_foreign` (`classroom_id`),
  KEY `fiche_cotations_course_id_foreign` (`course_id`),
  KEY `fiche_cotations_deliberation_id_foreign` (`deliberation_id`),
  CONSTRAINT `fiche_cotations_classroom_id_foreign` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fiche_cotations_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fiche_cotations_deliberation_id_foreign` FOREIGN KEY (`deliberation_id`) REFERENCES `deliberations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fiche_cotations_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`),
  CONSTRAINT `fiche_cotations_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `filiaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `filiaires` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `filiaires_level_name_index` (`name`),
  KEY `filiaires_school_id_foreign` (`school_id`),
  CONSTRAINT `filiaires_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fonctions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fonctions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `formation_continues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `formation_continues` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `academic_personal_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `classroom_id` bigint unsigned DEFAULT NULL,
  `filiere_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `formation_continues_academic_personal_id_foreign` (`academic_personal_id`),
  KEY `formation_continues_school_id_foreign` (`school_id`),
  KEY `formation_continues_classroom_id_foreign` (`classroom_id`),
  KEY `formation_continues_filiere_id_foreign` (`filiere_id`),
  CONSTRAINT `formation_continues_academic_personal_id_foreign` FOREIGN KEY (`academic_personal_id`) REFERENCES `academic_personals` (`id`),
  CONSTRAINT `formation_continues_classroom_id_foreign` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`),
  CONSTRAINT `formation_continues_filiere_id_foreign` FOREIGN KEY (`filiere_id`) REFERENCES `filiaires` (`id`),
  CONSTRAINT `formation_continues_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `immo_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `immo_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `immo_accounts_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `immo_ammortissemen_comptabilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `immo_ammortissemen_comptabilities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` double NOT NULL,
  `purchase_date` date NOT NULL,
  `number_years` int NOT NULL,
  `immo_account_id` bigint unsigned NOT NULL,
  `immo_sub_account_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `immo_ammortissemen_comptabilities_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `immo_sub_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `immo_sub_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `immo_sub_account_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `immo_sub_accounts_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `indiscipline_cases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `indiscipline_cases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `fault_count` int NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `classroom_id` bigint unsigned NOT NULL,
  `filiere_id` bigint unsigned DEFAULT NULL,
  `school_year_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `indiscipline_cases_student_id_foreign` (`student_id`),
  KEY `indiscipline_cases_classroom_id_foreign` (`classroom_id`),
  KEY `indiscipline_cases_filiere_id_foreign` (`filiere_id`),
  KEY `indiscipline_cases_school_year_id_foreign` (`school_year_id`),
  CONSTRAINT `indiscipline_cases_classroom_id_foreign` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `indiscipline_cases_filiere_id_foreign` FOREIGN KEY (`filiere_id`) REFERENCES `filiaires` (`id`) ON DELETE SET NULL,
  CONSTRAINT `indiscipline_cases_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `indiscipline_cases_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `infra_bailleurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `infra_bailleurs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `school_id` bigint unsigned NOT NULL,
  `academic_personal_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `infra_bailleurs_school_id_foreign` (`school_id`),
  KEY `infra_bailleurs_academic_personal_id_foreign` (`academic_personal_id`),
  CONSTRAINT `infra_bailleurs_academic_personal_id_foreign` FOREIGN KEY (`academic_personal_id`) REFERENCES `academic_personals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `infra_bailleurs_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `infra_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `infra_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `school_id` bigint unsigned NOT NULL,
  `academic_personal_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `infra_categories_school_id_foreign` (`school_id`),
  KEY `infra_categories_academic_personal_id_foreign` (`academic_personal_id`),
  CONSTRAINT `infra_categories_academic_personal_id_foreign` FOREIGN KEY (`academic_personal_id`) REFERENCES `academic_personals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `infra_categories_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `infra_equipements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `infra_equipements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_acquisition` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `montant_acquisition` double NOT NULL,
  `infra_bailleur_id` bigint unsigned NOT NULL,
  `infra_categorie_id` bigint unsigned NOT NULL,
  `emplacement` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `infra_equipements_code_unique` (`code`),
  KEY `infra_equipements_infra_bailleur_id_foreign` (`infra_bailleur_id`),
  KEY `infra_equipements_infra_categorie_id_foreign` (`infra_categorie_id`),
  KEY `infra_equipements_school_id_foreign` (`school_id`),
  CONSTRAINT `infra_equipements_infra_bailleur_id_foreign` FOREIGN KEY (`infra_bailleur_id`) REFERENCES `infra_bailleurs` (`id`),
  CONSTRAINT `infra_equipements_infra_categorie_id_foreign` FOREIGN KEY (`infra_categorie_id`) REFERENCES `infra_categories` (`id`),
  CONSTRAINT `infra_equipements_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `infra_equipment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `infra_equipment` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_id` bigint unsigned NOT NULL,
  `serial_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state_id` bigint unsigned DEFAULT NULL,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `infra_equipment_type_id_foreign` (`type_id`),
  KEY `infra_equipment_state_id_foreign` (`state_id`),
  KEY `infra_equipment_school_id_foreign` (`school_id`),
  KEY `infra_equipment_user_id_foreign` (`user_id`),
  CONSTRAINT `infra_equipment_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `infra_equipment_state_id_foreign` FOREIGN KEY (`state_id`) REFERENCES `infra_states` (`id`),
  CONSTRAINT `infra_equipment_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `infra_types` (`id`),
  CONSTRAINT `infra_equipment_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `infra_etats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `infra_etats` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `infra_infrastructure_id` bigint unsigned NOT NULL,
  `infra_iventaire_infrastructure_id` bigint unsigned NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `school_id` bigint unsigned NOT NULL,
  `academic_personal_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `infra_etats_infra_infrastructure_id_foreign` (`infra_infrastructure_id`),
  KEY `infra_etats_infra_iventaire_infrastructure_id_foreign` (`infra_iventaire_infrastructure_id`),
  KEY `infra_etats_school_id_foreign` (`school_id`),
  KEY `infra_etats_academic_personal_id_foreign` (`academic_personal_id`),
  CONSTRAINT `infra_etats_academic_personal_id_foreign` FOREIGN KEY (`academic_personal_id`) REFERENCES `academic_personals` (`id`),
  CONSTRAINT `infra_etats_infra_infrastructure_id_foreign` FOREIGN KEY (`infra_infrastructure_id`) REFERENCES `infra_infrastructures` (`id`),
  CONSTRAINT `infra_etats_infra_iventaire_infrastructure_id_foreign` FOREIGN KEY (`infra_iventaire_infrastructure_id`) REFERENCES `infra_iventaire_infrastructures` (`id`),
  CONSTRAINT `infra_etats_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `infra_infrastructures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `infra_infrastructures` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_construction` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `montant_construction` int NOT NULL,
  `emplacement` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `infra_categorie_id` bigint unsigned NOT NULL,
  `infra_bailleur_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `infra_infrastructures_infra_categorie_id_foreign` (`infra_categorie_id`),
  KEY `infra_infrastructures_infra_bailleur_id_foreign` (`infra_bailleur_id`),
  KEY `infra_infrastructures_school_id_foreign` (`school_id`),
  CONSTRAINT `infra_infrastructures_infra_bailleur_id_foreign` FOREIGN KEY (`infra_bailleur_id`) REFERENCES `infra_bailleurs` (`id`),
  CONSTRAINT `infra_infrastructures_infra_categorie_id_foreign` FOREIGN KEY (`infra_categorie_id`) REFERENCES `infra_categories` (`id`),
  CONSTRAINT `infra_infrastructures_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `infra_inventaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `infra_inventaires` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `infra_equipement_id` bigint unsigned NOT NULL,
  `observation` text COLLATE utf8mb4_unicode_ci,
  `school_id` bigint unsigned NOT NULL,
  `author_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `infra_inventaires_infra_equipement_id_foreign` (`infra_equipement_id`),
  KEY `infra_inventaires_school_id_foreign` (`school_id`),
  KEY `infra_inventaires_author_id_foreign` (`author_id`),
  CONSTRAINT `infra_inventaires_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `academic_personals` (`id`),
  CONSTRAINT `infra_inventaires_infra_equipement_id_foreign` FOREIGN KEY (`infra_equipement_id`) REFERENCES `infra_equipements` (`id`),
  CONSTRAINT `infra_inventaires_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `infra_inventories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `infra_inventories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_date` date NOT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `infra_inventories_school_id_foreign` (`school_id`),
  KEY `infra_inventories_user_id_foreign` (`user_id`),
  CONSTRAINT `infra_inventories_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `infra_inventories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `infra_inventory_equipment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `infra_inventory_equipment` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_id` bigint unsigned NOT NULL,
  `equipment_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `infra_inventory_equipment_inventory_id_foreign` (`inventory_id`),
  KEY `infra_inventory_equipment_equipment_id_foreign` (`equipment_id`),
  KEY `infra_inventory_equipment_school_id_foreign` (`school_id`),
  KEY `infra_inventory_equipment_user_id_foreign` (`user_id`),
  CONSTRAINT `infra_inventory_equipment_equipment_id_foreign` FOREIGN KEY (`equipment_id`) REFERENCES `infra_equipment` (`id`),
  CONSTRAINT `infra_inventory_equipment_inventory_id_foreign` FOREIGN KEY (`inventory_id`) REFERENCES `infra_inventories` (`id`),
  CONSTRAINT `infra_inventory_equipment_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `infra_inventory_equipment_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `infra_inventory_real_states`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `infra_inventory_real_states` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_id` bigint unsigned NOT NULL,
  `state_id` bigint unsigned NOT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `infra_inventory_real_states_inventory_id_foreign` (`inventory_id`),
  KEY `infra_inventory_real_states_state_id_foreign` (`state_id`),
  KEY `infra_inventory_real_states_school_id_foreign` (`school_id`),
  KEY `infra_inventory_real_states_user_id_foreign` (`user_id`),
  CONSTRAINT `infra_inventory_real_states_inventory_id_foreign` FOREIGN KEY (`inventory_id`) REFERENCES `infra_inventories` (`id`),
  CONSTRAINT `infra_inventory_real_states_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `infra_inventory_real_states_state_id_foreign` FOREIGN KEY (`state_id`) REFERENCES `infra_states` (`id`),
  CONSTRAINT `infra_inventory_real_states_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `infra_iventaire_infrastructures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `infra_iventaire_infrastructures` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `infra_infrastructure_id` bigint unsigned NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `school_id` bigint unsigned NOT NULL,
  `academic_personal_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `infra_iventaire_infrastructures_infra_infrastructure_id_foreign` (`infra_infrastructure_id`),
  KEY `infra_iventaire_infrastructures_school_id_foreign` (`school_id`),
  KEY `infra_iventaire_infrastructures_academic_personal_id_foreign` (`academic_personal_id`),
  CONSTRAINT `infra_iventaire_infrastructures_academic_personal_id_foreign` FOREIGN KEY (`academic_personal_id`) REFERENCES `academic_personals` (`id`),
  CONSTRAINT `infra_iventaire_infrastructures_infra_infrastructure_id_foreign` FOREIGN KEY (`infra_infrastructure_id`) REFERENCES `infra_infrastructures` (`id`),
  CONSTRAINT `infra_iventaire_infrastructures_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `infra_states`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `infra_states` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `infra_states_school_id_foreign` (`school_id`),
  KEY `infra_states_user_id_foreign` (`user_id`),
  CONSTRAINT `infra_states_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `infra_states_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `infra_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `infra_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `infra_types_school_id_foreign` (`school_id`),
  KEY `infra_types_user_id_foreign` (`user_id`),
  CONSTRAINT `infra_types_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `infra_types_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_offers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_offers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` bigint unsigned NOT NULL,
  `is_open` tinyint(1) NOT NULL DEFAULT '1',
  `school_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_offers_company_id_foreign` (`company_id`),
  CONSTRAINT `job_offers_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `journals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `journals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `input_account_id` bigint unsigned NOT NULL,
  `output_account_id` bigint unsigned NOT NULL,
  `account_plan_id` bigint unsigned NOT NULL,
  `sub_account_plan_id` bigint unsigned NOT NULL,
  `account_id` bigint unsigned NOT NULL,
  `abandoned` tinyint(1) NOT NULL DEFAULT '0',
  `linked_journal_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `journals_linked_journal_id_foreign` (`linked_journal_id`),
  CONSTRAINT `journals_linked_journal_id_foreign` FOREIGN KEY (`linked_journal_id`) REFERENCES `journals` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mecanisations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mecanisations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `media` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `collection_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `conversions_disk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint unsigned NOT NULL,
  `manipulations` json NOT NULL,
  `custom_properties` json NOT NULL,
  `generated_conversions` json NOT NULL,
  `responsive_images` json NOT NULL,
  `order_column` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_uuid_unique` (`uuid`),
  KEY `media_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `media_order_column_index` (`order_column`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mois`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mois` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mois_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `parents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `firstname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `genre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `identity_card` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `parents_phone_number_unique` (`phone_number`),
  UNIQUE KEY `parents_identity_card_unique` (`identity_card`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payment_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_methods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_methods_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payment_motifs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_motifs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fee_type_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_motifs_code_unique` (`code`),
  KEY `payment_motifs_fee_type_id_foreign` (`fee_type_id`),
  CONSTRAINT `payment_motifs_fee_type_id_foreign` FOREIGN KEY (`fee_type_id`) REFERENCES `fee_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fee_id` bigint unsigned NOT NULL,
  `payment_method_id` bigint unsigned NOT NULL,
  `currency_id` bigint unsigned NOT NULL,
  `amount` double NOT NULL,
  `remaining_amount` double NOT NULL DEFAULT '0',
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `paid_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','confirmed','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'confirmed',
  `confirmed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `refunded_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `student_id` bigint unsigned DEFAULT NULL,
  `classroom_id` bigint unsigned DEFAULT NULL,
  `school_year_id` bigint unsigned DEFAULT NULL,
  `account_id` bigint unsigned NOT NULL,
  `rental_contract_id` bigint unsigned DEFAULT NULL,
  `rental_payment_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rental_amount` decimal(15,2) DEFAULT NULL,
  `rental_due_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_reference_unique` (`reference`),
  KEY `payments_fee_id_foreign` (`fee_id`),
  KEY `payments_payment_method_id_foreign` (`payment_method_id`),
  KEY `payments_currency_id_foreign` (`currency_id`),
  KEY `payments_student_id_foreign` (`student_id`),
  KEY `payments_classroom_id_foreign` (`classroom_id`),
  KEY `payments_school_year_id_foreign` (`school_year_id`),
  KEY `payments_rental_contract_id_foreign` (`rental_contract_id`),
  CONSTRAINT `payments_classroom_id_foreign` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  CONSTRAINT `payments_fee_id_foreign` FOREIGN KEY (`fee_id`) REFERENCES `fees` (`id`),
  CONSTRAINT `payments_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`),
  CONSTRAINT `payments_rental_contract_id_foreign` FOREIGN KEY (`rental_contract_id`) REFERENCES `rental_contracts` (`id`),
  CONSTRAINT `payments_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `person_affectations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `person_affectations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lieu_affectation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `durree_jours` int NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `date_debut` date DEFAULT NULL,
  `school_year_id` bigint unsigned NOT NULL,
  `author_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `school_id` bigint unsigned DEFAULT NULL,
  `academic_personal_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `person_affectations_school_year_id_foreign` (`school_year_id`),
  KEY `person_affectations_author_id_foreign` (`author_id`),
  KEY `person_affectations_school_id_foreign` (`school_id`),
  KEY `person_affectations_academic_personal_id_foreign` (`academic_personal_id`),
  CONSTRAINT `person_affectations_academic_personal_id_foreign` FOREIGN KEY (`academic_personal_id`) REFERENCES `academic_personals` (`id`) ON DELETE SET NULL,
  CONSTRAINT `person_affectations_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `personals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `person_affectations_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  CONSTRAINT `person_affectations_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `person_conges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `person_conges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `academic_personal_id` bigint unsigned NOT NULL,
  `jour_demand` int NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `school_id` bigint unsigned NOT NULL,
  `author_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `creer_a` date DEFAULT NULL,
  `school_year_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `person_conges_school_id_foreign` (`school_id`),
  KEY `person_conges_school_year_id_foreign` (`school_year_id`),
  KEY `person_conges_academic_personal_id_foreign` (`academic_personal_id`),
  KEY `person_conges_author_id_foreign` (`author_id`),
  CONSTRAINT `person_conges_academic_personal_id_foreign` FOREIGN KEY (`academic_personal_id`) REFERENCES `academic_personals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `person_conges_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `academic_personals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `person_conges_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `person_conges_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `person_evaluations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `person_evaluations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `critiques` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `c1_quantite_travail` double DEFAULT NULL,
  `c2_theorie_pratique` double DEFAULT NULL,
  `c3_determ_ress_perso` double DEFAULT NULL,
  `c4_ponctualite` double DEFAULT NULL,
  `c5_dr_att_posit_collab` double DEFAULT NULL,
  `school_year_id` bigint unsigned NOT NULL,
  `semester_id` bigint unsigned DEFAULT NULL,
  `school_id` bigint unsigned NOT NULL,
  `author_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `person_evaluations_school_year_id_foreign` (`school_year_id`),
  KEY `person_evaluations_semester_id_foreign` (`semester_id`),
  KEY `person_evaluations_school_id_foreign` (`school_id`),
  KEY `person_evaluations_author_id_foreign` (`author_id`),
  CONSTRAINT `person_evaluations_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `personals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `person_evaluations_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `person_evaluations_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `person_evaluations_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `person_liste_salaries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `person_liste_salaries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `personal_id` bigint unsigned NOT NULL,
  `person_salaire_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `author_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `person_liste_salaries_personal_id_foreign` (`personal_id`),
  KEY `person_liste_salaries_person_salaire_id_foreign` (`person_salaire_id`),
  KEY `person_liste_salaries_school_id_foreign` (`school_id`),
  KEY `person_liste_salaries_author_id_foreign` (`author_id`),
  CONSTRAINT `person_liste_salaries_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `personals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `person_liste_salaries_person_salaire_id_foreign` FOREIGN KEY (`person_salaire_id`) REFERENCES `person_salaires` (`id`) ON DELETE CASCADE,
  CONSTRAINT `person_liste_salaries_personal_id_foreign` FOREIGN KEY (`personal_id`) REFERENCES `personals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `person_liste_salaries_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `person_presences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `person_presences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `personnel_id` bigint unsigned NOT NULL,
  `presence` tinyint(1) NOT NULL,
  `sick` tinyint(1) NOT NULL DEFAULT '0',
  `absences_justified` tinyint(1) NOT NULL DEFAULT '0',
  `school_id` bigint unsigned NOT NULL,
  `author_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `absent_justified` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `person_presences_school_id_foreign` (`school_id`),
  KEY `person_presences_personnel_id_foreign` (`personnel_id`),
  KEY `person_presences_author_id_foreign` (`author_id`),
  CONSTRAINT `person_presences_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `academic_personals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `person_presences_personnel_id_foreign` FOREIGN KEY (`personnel_id`) REFERENCES `academic_personals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `person_presences_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `person_salaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `person_salaires` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mois_id` bigint unsigned NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `school_year_id` bigint unsigned NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `author_id` bigint unsigned NOT NULL,
  `academic_personal_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `person_salaires_mois_id_foreign` (`mois_id`),
  KEY `person_salaires_school_year_id_foreign` (`school_year_id`),
  KEY `person_salaires_author_id_foreign` (`author_id`),
  KEY `person_salaires_academic_personal_id_foreign` (`academic_personal_id`),
  CONSTRAINT `person_salaires_academic_personal_id_foreign` FOREIGN KEY (`academic_personal_id`) REFERENCES `academic_personals` (`id`),
  CONSTRAINT `person_salaires_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `personals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `person_salaires_mois_id_foreign` FOREIGN KEY (`mois_id`) REFERENCES `mois` (`id`) ON DELETE CASCADE,
  CONSTRAINT `person_salaires_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `matricule` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pre_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `civil_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_id` bigint unsigned NOT NULL,
  `province_id` bigint unsigned NOT NULL,
  `territory_id` bigint unsigned NOT NULL,
  `commune_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned DEFAULT NULL,
  `type_id` bigint unsigned NOT NULL,
  `physical_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `birth_date` date NOT NULL,
  `birth_place` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `identity_card_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `father_id` bigint unsigned DEFAULT NULL,
  `mother_id` bigint unsigned DEFAULT NULL,
  `academic_level_id` bigint unsigned DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fonction_id` bigint unsigned NOT NULL,
  `mechanisation_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personals_matricule_unique` (`matricule`),
  KEY `personals_country_id_foreign` (`country_id`),
  KEY `personals_province_id_foreign` (`province_id`),
  KEY `personals_territory_id_foreign` (`territory_id`),
  KEY `personals_commune_id_foreign` (`commune_id`),
  KEY `personals_school_id_foreign` (`school_id`),
  KEY `personals_type_id_foreign` (`type_id`),
  KEY `personals_father_id_foreign` (`father_id`),
  KEY `personals_mother_id_foreign` (`mother_id`),
  KEY `personals_academic_level_id_foreign` (`academic_level_id`),
  KEY `personals_fonction_id_foreign` (`fonction_id`),
  KEY `personals_mechanisation_id_foreign` (`mechanisation_id`),
  CONSTRAINT `personals_academic_level_id_foreign` FOREIGN KEY (`academic_level_id`) REFERENCES `academic_levels` (`id`) ON DELETE SET NULL,
  CONSTRAINT `personals_commune_id_foreign` FOREIGN KEY (`commune_id`) REFERENCES `communes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `personals_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `personals_father_id_foreign` FOREIGN KEY (`father_id`) REFERENCES `personals` (`id`) ON DELETE SET NULL,
  CONSTRAINT `personals_fonction_id_foreign` FOREIGN KEY (`fonction_id`) REFERENCES `fonctions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `personals_mechanisation_id_foreign` FOREIGN KEY (`mechanisation_id`) REFERENCES `mecanisations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `personals_mother_id_foreign` FOREIGN KEY (`mother_id`) REFERENCES `personals` (`id`) ON DELETE SET NULL,
  CONSTRAINT `personals_province_id_foreign` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE CASCADE,
  CONSTRAINT `personals_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL,
  CONSTRAINT `personals_territory_id_foreign` FOREIGN KEY (`territory_id`) REFERENCES `territories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `personals_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `presences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `presences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `presence` tinyint(1) NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `classroom_id` bigint unsigned NOT NULL,
  `academic_level_id` bigint unsigned DEFAULT NULL,
  `absent_justified` tinyint(1) NOT NULL DEFAULT '0',
  `sick` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `presences_student_id_foreign` (`student_id`),
  KEY `presences_school_id_foreign` (`school_id`),
  KEY `presences_classroom_id_foreign` (`classroom_id`),
  KEY `presences_academic_level_id_foreign` (`academic_level_id`),
  CONSTRAINT `presences_academic_level_id_foreign` FOREIGN KEY (`academic_level_id`) REFERENCES `academic_levels` (`id`) ON DELETE SET NULL,
  CONSTRAINT `presences_classroom_id_foreign` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `presences_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `presences_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `projects_school_id_foreign` (`school_id`),
  KEY `projects_user_id_foreign` (`user_id`),
  CONSTRAINT `projects_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `projects_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `provinces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `provinces` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `country_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `provinces_country_id_name_unique` (`country_id`,`name`),
  CONSTRAINT `provinces_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `registration_parents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `registration_parents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `registration_id` bigint unsigned NOT NULL,
  `parent1_id` bigint unsigned NOT NULL,
  `parent2_id` bigint unsigned DEFAULT NULL,
  `parent3_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `registration_parents_registration_id_foreign` (`registration_id`),
  KEY `registration_parents_parent1_id_foreign` (`parent1_id`),
  KEY `registration_parents_parent2_id_foreign` (`parent2_id`),
  KEY `registration_parents_parent3_id_foreign` (`parent3_id`),
  CONSTRAINT `registration_parents_parent1_id_foreign` FOREIGN KEY (`parent1_id`) REFERENCES `parents` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `registration_parents_parent2_id_foreign` FOREIGN KEY (`parent2_id`) REFERENCES `parents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `registration_parents_parent3_id_foreign` FOREIGN KEY (`parent3_id`) REFERENCES `parents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `registration_parents_registration_id_foreign` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `registrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `classroom_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `school_year_id` bigint unsigned NOT NULL,
  `academic_personal_id` bigint unsigned NOT NULL,
  `academic_level_id` bigint unsigned NOT NULL,
  `type_id` bigint unsigned NOT NULL,
  `registration_date` date NOT NULL,
  `registration_status` tinyint(1) NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `filiaire_id` bigint unsigned DEFAULT NULL,
  `cycle_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `registrations_school_id_foreign` (`school_id`),
  KEY `registrations_student_id_foreign` (`student_id`),
  KEY `registrations_school_year_id_foreign` (`school_year_id`),
  KEY `registrations_academic_personal_id_foreign` (`academic_personal_id`),
  KEY `registrations_academic_level_id_foreign` (`academic_level_id`),
  KEY `registrations_type_id_foreign` (`type_id`),
  KEY `registrations_classroom_id_foreign` (`classroom_id`),
  KEY `registrations_filiaire_id_foreign` (`filiaire_id`),
  KEY `registrations_cycle_id_foreign` (`cycle_id`),
  CONSTRAINT `registrations_academic_level_id_foreign` FOREIGN KEY (`academic_level_id`) REFERENCES `academic_levels` (`id`),
  CONSTRAINT `registrations_academic_personal_id_foreign` FOREIGN KEY (`academic_personal_id`) REFERENCES `academic_personals` (`id`),
  CONSTRAINT `registrations_classroom_id_foreign` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `registrations_cycle_id_foreign` FOREIGN KEY (`cycle_id`) REFERENCES `cycles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `registrations_filiaire_id_foreign` FOREIGN KEY (`filiaire_id`) REFERENCES `filiaires` (`id`) ON DELETE SET NULL,
  CONSTRAINT `registrations_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `registrations_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`),
  CONSTRAINT `registrations_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  CONSTRAINT `registrations_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rental_contract_equipment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rental_contract_equipment` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rental_contract_id` bigint unsigned NOT NULL,
  `equipment_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rental_contract_equipment_rental_contract_id_foreign` (`rental_contract_id`),
  KEY `rental_contract_equipment_equipment_id_foreign` (`equipment_id`),
  KEY `rental_contract_equipment_school_id_foreign` (`school_id`),
  KEY `rental_contract_equipment_user_id_foreign` (`user_id`),
  CONSTRAINT `rental_contract_equipment_equipment_id_foreign` FOREIGN KEY (`equipment_id`) REFERENCES `equipments` (`id`),
  CONSTRAINT `rental_contract_equipment_rental_contract_id_foreign` FOREIGN KEY (`rental_contract_id`) REFERENCES `rental_contracts` (`id`),
  CONSTRAINT `rental_contract_equipment_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `rental_contract_equipment_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rental_contracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rental_contracts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rental_contracts_client_id_foreign` (`client_id`),
  KEY `rental_contracts_school_id_foreign` (`school_id`),
  KEY `rental_contracts_user_id_foreign` (`user_id`),
  CONSTRAINT `rental_contracts_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `rental_contracts_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `rental_contracts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `repechages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `repechages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned DEFAULT NULL,
  `school_year_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filiaire_id` bigint unsigned DEFAULT NULL,
  `classroom_id` bigint unsigned DEFAULT NULL,
  `course_id` bigint unsigned DEFAULT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `score_percent` double DEFAULT NULL,
  `student_score` double DEFAULT NULL,
  `is_eliminated` tinyint(1) NOT NULL DEFAULT '0',
  `cycle_id` bigint unsigned DEFAULT NULL,
  `school_id` bigint unsigned DEFAULT NULL,
  `academic_level_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `school_years`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `school_years` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `school_years_school_id_name_unique` (`school_id`,`name`),
  CONSTRAINT `school_years_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schools` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `country_id` bigint unsigned NOT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `province_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_id` bigint unsigned NOT NULL,
  `longitude` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `schools_name_unique` (`name`),
  UNIQUE KEY `schools_phone_number_unique` (`phone_number`),
  UNIQUE KEY `schools_email_unique` (`email`),
  KEY `schools_country_id_foreign` (`country_id`),
  KEY `schools_type_id_foreign` (`type_id`),
  KEY `schools_province_id_foreign` (`province_id`),
  CONSTRAINT `schools_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`),
  CONSTRAINT `schools_province_id_foreign` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE SET NULL,
  CONSTRAINT `schools_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `semesters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `semesters` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `semesters_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stock_articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_articles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `provider_id` bigint unsigned NOT NULL,
  `min_threshold` int NOT NULL DEFAULT '0',
  `max_threshold` int DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_articles_category_id_foreign` (`category_id`),
  KEY `stock_articles_provider_id_foreign` (`provider_id`),
  KEY `stock_articles_school_id_foreign` (`school_id`),
  KEY `stock_articles_user_id_foreign` (`user_id`),
  CONSTRAINT `stock_articles_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `stock_categories` (`id`),
  CONSTRAINT `stock_articles_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `stock_providers` (`id`),
  CONSTRAINT `stock_articles_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `stock_articles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stock_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_categories_school_id_foreign` (`school_id`),
  KEY `stock_categories_user_id_foreign` (`user_id`),
  CONSTRAINT `stock_categories_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `stock_categories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stock_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_entries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `article_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL,
  `entry_date` date NOT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_entries_article_id_foreign` (`article_id`),
  KEY `stock_entries_school_id_foreign` (`school_id`),
  KEY `stock_entries_user_id_foreign` (`user_id`),
  CONSTRAINT `stock_entries_article_id_foreign` FOREIGN KEY (`article_id`) REFERENCES `stock_articles` (`id`),
  CONSTRAINT `stock_entries_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `stock_entries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stock_exits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_exits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `article_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL,
  `exit_date` date NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_exits_article_id_foreign` (`article_id`),
  KEY `stock_exits_school_id_foreign` (`school_id`),
  KEY `stock_exits_user_id_foreign` (`user_id`),
  CONSTRAINT `stock_exits_article_id_foreign` FOREIGN KEY (`article_id`) REFERENCES `stock_articles` (`id`),
  CONSTRAINT `stock_exits_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `stock_exits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stock_inventories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_inventories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_date` date NOT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_inventories_school_id_foreign` (`school_id`),
  KEY `stock_inventories_user_id_foreign` (`user_id`),
  CONSTRAINT `stock_inventories_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `stock_inventories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stock_providers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_providers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_providers_school_id_foreign` (`school_id`),
  KEY `stock_providers_user_id_foreign` (`user_id`),
  CONSTRAINT `stock_providers_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `stock_providers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stock_states`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_states` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `article_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL,
  `state_date` date NOT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `school_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_states_article_id_foreign` (`article_id`),
  KEY `stock_states_school_id_foreign` (`school_id`),
  KEY `stock_states_user_id_foreign` (`user_id`),
  CONSTRAINT `stock_states_article_id_foreign` FOREIGN KEY (`article_id`) REFERENCES `stock_articles` (`id`),
  CONSTRAINT `stock_states_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`),
  CONSTRAINT `stock_states_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `student_exits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_exits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `exit_time` time NOT NULL,
  `motif` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filiere_id` bigint unsigned DEFAULT NULL,
  `school_year_id` bigint unsigned NOT NULL,
  `semester` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_exits_student_id_foreign` (`student_id`),
  KEY `student_exits_filiere_id_foreign` (`filiere_id`),
  KEY `student_exits_school_year_id_foreign` (`school_year_id`),
  CONSTRAINT `student_exits_filiere_id_foreign` FOREIGN KEY (`filiere_id`) REFERENCES `filiaires` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_exits_school_year_id_foreign` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_exits_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `students` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `province_id` bigint unsigned NOT NULL,
  `territory_id` bigint unsigned NOT NULL,
  `commune_id` bigint unsigned NOT NULL,
  `parents_id` bigint unsigned DEFAULT NULL,
  `parent2_id` bigint unsigned DEFAULT NULL,
  `parent3_id` bigint unsigned DEFAULT NULL,
  `matricule` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `firstname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lastname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `civil_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `birth_date` date NOT NULL,
  `birth_place` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `school_id` bigint unsigned NOT NULL,
  `country_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `students_matricule_unique` (`matricule`),
  UNIQUE KEY `students_phone_number_unique` (`phone_number`),
  UNIQUE KEY `students_email_unique` (`email`),
  KEY `students_province_id_foreign` (`province_id`),
  KEY `students_territory_id_foreign` (`territory_id`),
  KEY `students_commune_id_foreign` (`commune_id`),
  KEY `students_parents_id_foreign` (`parents_id`),
  KEY `students_parent2_id_foreign` (`parent2_id`),
  KEY `students_parent3_id_foreign` (`parent3_id`),
  CONSTRAINT `students_commune_id_foreign` FOREIGN KEY (`commune_id`) REFERENCES `communes` (`id`),
  CONSTRAINT `students_parent2_id_foreign` FOREIGN KEY (`parent2_id`) REFERENCES `parents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `students_parent3_id_foreign` FOREIGN KEY (`parent3_id`) REFERENCES `parents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `students_parents_id_foreign` FOREIGN KEY (`parents_id`) REFERENCES `parents` (`id`),
  CONSTRAINT `students_province_id_foreign` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`),
  CONSTRAINT `students_territory_id_foreign` FOREIGN KEY (`territory_id`) REFERENCES `territories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sub_account_plan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sub_account_plan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_plan_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `territories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `territories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `province_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `territories_province_id_name_unique` (`province_id`,`name`),
  CONSTRAINT `territories_province_id_foreign` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `types_title_unique` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_login_at` date DEFAULT NULL,
  `school_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_school_id_foreign` (`school_id`),
  CONSTRAINT `users_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `validation_aureats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `validation_aureats` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `registration_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` enum('male','female','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cycle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `present` tinyint(1) NOT NULL DEFAULT '0',
  `percentage` tinyint unsigned DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `va_class_percentage_idx` (`class`,`percentage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `visits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `visits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `personal_id` bigint unsigned NOT NULL,
  `classroom_id` bigint unsigned NOT NULL,
  `school_id` bigint unsigned NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `visiteur` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cot_doc_prof` double NOT NULL,
  `cot_doc_eleve` int DEFAULT NULL,
  `cot_meth_proc` double NOT NULL,
  `cot_matiere` double NOT NULL,
  `cot_march_lecon` double NOT NULL,
  `cot_enseignant` double NOT NULL,
  `cot_eleve` double NOT NULL,
  `visit_hour` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `datefin` date DEFAULT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `fonction_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `visits_personal_id_foreign` (`personal_id`),
  KEY `visits_classroom_id_foreign` (`classroom_id`),
  KEY `visits_school_id_foreign` (`school_id`),
  KEY `visits_fonction_id_foreign` (`fonction_id`),
  CONSTRAINT `visits_classroom_id_foreign` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `visits_fonction_id_foreign` FOREIGN KEY (`fonction_id`) REFERENCES `fonctions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `visits_personal_id_foreign` FOREIGN KEY (`personal_id`) REFERENCES `academic_personals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `visits_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2025_06_06_115906_create_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2025_06_06_115907_add_event_column_to_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2025_06_06_115908_add_batch_uuid_column_to_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2025_06_06_120047_create_media_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2025_06_10_074427_create_countries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2025_06_10_074737_create_provinces_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2025_06_10_075749_create_communes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2025_06_10_080058_create_territories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2025_06_10_080614_create_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2025_06_10_081610_create_fonctions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2025_06_10_082903_create_schools_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_06_10_083532_create_filiaires_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_06_10_084157_create_classrooms_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_06_10_085517_create_parents_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_06_10_090247_create_academic_levels_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_06_10_092513_create_academic_personals_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_06_10_100948_create_students_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_06_10_101753_create_documents_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_06_10_102318_add_fields_to_students_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_06_10_102456_add_fields_to_documents_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_06_10_102808_create_disciplines_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_06_10_103543_create_school_years_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_06_10_103544_create_registrations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2025_06_10_104345_add_fields_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2025_06_10_110151_create_personal_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2025_07_07_200831_create_class_accounts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2025_07_07_205252_create_account_numbers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2025_07_08_115541_create_account_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2025_07_16_082150_create_payment_methods_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2025_07_16_083529_create_fee_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2025_07_16_085429_create_payment_motifs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2025_07_22_084145_create_currencies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2025_07_22_084235_create_exchange_rates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2025_07_23_065034_create_fees_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2025_07_25_100640_create_payments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2025_07_25_100742_create_expenses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2025_09_04_000000_create_visits_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2025_09_04_000001_create_disciplinary_actions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2025_09_04_073620_create_presences_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2025_09_09_000001_create_indiscipline_cases_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2025_09_09_000001_create_mecanisations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2025_09_09_000002_create_abandon_cases_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2025_09_09_000002_create_personals_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2025_09_09_000003_create_courses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2025_09_10_000003_create_student_exits_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2025_09_10_000004_create_person_presences_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2025_09_10_072347_create_conduites_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2025_09_11_000001_create_conduite_semesters_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2025_09_12_000001_create_person_conges_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2025_09_12_074437_create_semesters_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2025_09_12_075927_create_person_evaluations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2025_09_12_121749_create_mois',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2025_09_12_134607_person_salaires',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2025_09_12_134803_person_liste_salaires',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2025_09_12_141203_create_person_affectations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2025_09_13_000002_create_conduite_grades_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2025_09_15_114542_infra_categorie',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2025_09_15_123221_create_infra_bailleurs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2025_09_15_124838_create_infra_equipements_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2025_09_15_130054_create_infra_inventaires_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2025_09_15_131206_create_infra_infrastructures_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2025_09_15_131920_create_infra_iventaire_infrastructures_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2025_09_15_132544_create_infra_etats_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2025_09_17_000001_alter_visit_hour_type_in_visits_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2025_09_22_104807_add_province_fields',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2025_09_22_122119_create_permission_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2025_09_22_160000_update_unique_indexes_on_provinces_and_territories',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2025_09_22_161500_update_unique_index_on_communes',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2025_09_22_170500_update_unique_index_on_classrooms',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2025_09_23_000010_fix_courses_teacher_author_fk',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2025_09_23_130039_create_fiche_cotations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2025_09_23_131358_create_validation_aureats_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2025_09_23_144622_create_repechages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2025_09_24_000100_change_abandon_cases_semester_to_foreign',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2025_09_24_001000_change_conduite_semesters_semester_to_foreign',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2025_09_24_001100_add_conduite_id_to_conduite_semesters',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2025_09_26_085547_remove_academic_personal_id_from_students_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2025_09_26_120000_add_school_id_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2025_09_27_000001_add_classroom_id_to_presences_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2025_09_27_135005_add_school_id_in_student',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2025_09_29_000001_update_filiaire_foreign_on_classrooms',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2025_09_29_000002_update_classroom_foreign_on_registrations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2025_09_29_150445_add_country_in_student',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2025_09_30_000001_modify_visit_hour_on_fiche_cotations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2025_10_02_000000_add_unique_index_to_types_title',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2025_10_10_000001_add_image_to_students_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2025_10_10_120000_update_payments_table_add_student_context_and_drop_columns',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (91,'2025_10_10_120100_add_exchange_rate_to_fees_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (92,'2025_10_11_000003_create_cycles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (93,'2025_10_11_000004_add_cycle_id_to_academic_levels_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (94,'2025_10_11_000005_add_academic_level_id_to_filiaires_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2025_10_11_120500_ensure_cycle_id_on_academic_levels',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2025_10_11_130000_update_unique_index_on_classrooms_include_filiaire',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (97,'2025_10_11_130100_add_index_on_filiaires_academic_level_id_name',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (98,'2025_10_11_130200_update_unique_on_filiaires_level_name',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (99,'2025_10_11_131000_add_filiere_and_cycle_to_registrations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (100,'2025_10_13_000001_drop_label_from_fees_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (101,'2025_10_13_204430_drop_school_id_from_classrooms',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (102,'2025_10_15_092552_drop_table_account_number',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (103,'2025_10_15_101822_add_is_active_to_fee_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (104,'2025_10_15_103807_drop_account_type',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (105,'2025_10_15_104037_create_tables_accounts',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (106,'2025_10_16_090953_create_class_comptability',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (107,'2025_10_16_091924_create_category_comptability',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (108,'2025_10_16_092459_create_account_plan',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (109,'2025_10_16_101349_add_field_account',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (110,'2025_10_16_160000_create_academic_personal_course_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (111,'2025_10_16_170000_remove_teacher_id_from_courses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (112,'2025_10_18_125453_create_sub_account_plan',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (113,'2025_10_18_141818_input_account',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (114,'2025_10_19_000000_create_deliberations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (115,'2025_10_19_000002_add_deliberation_id_to_fiche_cotations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (116,'2025_10_19_132112_output_account',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (117,'2025_10_22_130015_add_school_id_in_account_plan',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (118,'2025_10_22_132150_create_analytic_plan',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (119,'2025_10_22_133655_create_budget_comptability',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (120,'2025_10_22_134009_create_a_new_comptability',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (121,'2025_10_22_135242_add_school_id_in_class_comptability',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (122,'2025_10_22_135537_add_school_id_in_category_comptability',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (123,'2025_10_24_121650_create_journals_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (124,'2025_10_24_152119_remove_semester_id_from_fiche_cotations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (125,'2025_10_24_153000_alter_note_column_type_in_fiche_cotations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (126,'2025_10_24_153300_remove_maxima_column_from_fiche_cotations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (127,'2025_10_27_000001_rename_columns_in_courses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (128,'2025_10_27_000002_add_cycle_id_to_courses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (129,'2025_11_01_000001_add_linked_journal_id_to_journals_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (130,'2025_11_01_120000_create_credits_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (131,'2025_11_01_121000_create_debits_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (132,'2025_11_04_144031_create_exercices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (133,'2025_11_04_152403_create_immo_accounts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (134,'2025_11_04_152413_create_immo_sub_accounts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (135,'2025_11_04_152420_create_immo_ammortissemen_comptabilities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (136,'2025_11_05_000001_add_status_booleans_to_presences_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (137,'2025_11_05_000003_add_abandoned_flag_to_journals_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (138,'2025_11_05_000100_alter_action_indiscipline_cases_to_string',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (139,'2025_11_05_063138_create_annalytique_comptabilities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (140,'2025_11_05_063214_create_ammortissement_comptabilities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (141,'2025_11_06_000001_add_percentage_to_validation_aureats_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (142,'2025_11_15_090000_add_student_score_to_repechages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (143,'2025_11_15_091000_create_registration_parents_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (144,'2025_11_19_000100_add_visteur_and_fonction_id_to_visits_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (145,'2025_11_25_000001_add_parent2_and_parent3_id_to_students_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (146,'2025_11_25_000002_remove_author_id_from_courses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (147,'2025_11_25_091205_create_notifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (148,'2025_11_26_000002_add_mechanisation_and_string_parents_to_academic_personals_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (149,'2025_11_26_000003_add_academic_personal_id_to_person_salaire_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (150,'2025_11_29_000001_add_sick_absences_justified_to_person_presences_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (151,'2025_12_03_073429_add_creer_a_to_person_conges_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (152,'2025_12_03_073632_add_school_year_id_to_person_conges_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (153,'2025_12_03_074532_add_flags_to_person_presences_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (154,'2025_12_03_120000_update_person_presences_foreign_keys',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (155,'2025_12_03_121000_update_person_conges_foreign_keys',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (156,'2025_12_06_000000_add_date_debut_to_person_affectations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (157,'2025_12_06_000001_add_school_id_to_person_affectations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (158,'2025_12_06_000002_add_academic_personal_id_to_person_affectations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (159,'2025_12_06_000003_remove_personal_id_from_person_affectations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (160,'2025_12_06_075212_add_datefin_to_visits_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (161,'2025_12_06_092843_add_cot_doc_eleve_to_visits_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (162,'2025_12_06_100312_fix_academic_personals_columns',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (163,'2025_12_06_111857_create_formation_continues_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (164,'2025_12_14_000001_add_chainage_fields_to_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (165,'2025_12_14_000001_add_indicator_to_classrooms_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (166,'2025_12_14_000001_remove_academic_level_id_from_filiaires_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (167,'2025_12_14_000002_add_titulaire_id_to_classrooms_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (168,'2025_12_14_000002_remove_old_chainage_fields',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (169,'2025_12_14_000003_replace_filiere_id_with_academic_level_id_in_presences_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (170,'2025_12_14_100000_add_cycle_id_to_academic_levels',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (171,'2025_12_14_200000_add_code_to_filiaires_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (172,'2025_12_26_215506_create_stock_providers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (173,'2025_12_26_215507_create_stock_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (174,'2025_12_26_215508_create_stock_articles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (175,'2025_12_26_215509_create_stock_entries_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (176,'2025_12_26_215509_create_stock_exits_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (177,'2025_12_26_215510_create_stock_inventories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (178,'2025_12_26_215510_create_stock_states_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (179,'2025_12_26_215518_create_infra_states_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (180,'2025_12_26_215519_create_infra_types_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (181,'2025_12_26_215520_create_infra_equipment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (182,'2025_12_26_215521_create_infra_inventories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (183,'2025_12_26_215521_create_infra_inventory_equipment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (184,'2025_12_26_215522_create_infra_inventory_real_states_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (185,'2025_12_27_000001_create_candidates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (186,'2025_12_27_000002_create_companies_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (187,'2025_12_27_000003_create_job_offers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (188,'2025_12_27_000004_create_applications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (189,'2025_12_28_000001_create_equipments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (190,'2025_12_28_000002_create_clients_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (191,'2025_12_28_000003_create_rental_contracts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (192,'2025_12_28_000004_create_rental_contract_equipment_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (193,'2025_12_28_000005_create_payments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (194,'2025_12_28_000006_create_projects_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (195,'2025_12_28_130000_drop_filiaires_level_name_unique',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (196,'2025_12_28_000001_cleanup_orphan_registrations',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (197,'2025_12_28_000001_add_school_year_id_to_input_output_accounts',3);
