---
name: ui-ux
description: Standards UI/UX de l'outil interne Bolli Rental — hiérarchie visuelle, états d'écran, tableaux et filtres, formulaires, accessibilité, ergonomique, responsive.
when_to_use: Avant de concevoir ou modifier un écran, une liste, un formulaire, un tableau de bord, un composant d'interface, ou quand on juge de la lisibilité et de l'ergonomie d'une vue.
---

Cible : un agent du service client, au téléphone, qui doit enregistrer un appel en moins de 30 secondes. L'outil est interne, pas une vitrine. Le cahier des charges retient comme critère « UX simple et fonctionnelle », pas un design abouti.

Priorité : **fonctionnalité > clarté > cohérence > responsive > performance**. Rien de décoratif au détriment de ces cinq points.

## Les quatre états — aucun écran n'en oublie un

| État       | Traitement                                                                                 |
| ---------- | ------------------------------------------------------------------------------------------ |
| Chargement | squelette ou indicateur discret, jamais un saut de mise en page                            |
| Vide       | phrase utile + action principale (« Aucun appel sur cette période. Enregistrer un appel ») |
| Erreur     | message compréhensible par un non-technicien + action de reprise                           |
| Rempli     | le cas nominal                                                                             |

## Tableau des appels

- Colonnes utiles seulement : date/heure, client, agent, sens, motif, durée, statut, étiquettes.
- Statut et sens rendus par un badge de couleur **plus un libellé texte** — la couleur seule exclut les daltoniens.
- Ligne entière cliquable vers le détail, avec `focus-visible` au clavier.
- Durée formatée pour un humain (`4 min 12 s`), jamais en secondes brutes.
- Tri sur date par défaut, décroissant.
- Pagination visible avec compteur total.

## Filtres

- Barre de filtres persistante, alignée sur une ligne en desktop, repliable en mobile.
- Filtres actifs affichés sous forme de puces supprimables, plus un « Réinitialiser ».
- Recherche texte avec debounce ~300 ms.
- L'état des filtres vit dans l'URL : rechargeable, partageable entre agents.

## Formulaire d'appel

- Un seul écran, ordre logique : client → réservation (optionnel) → sens → motif → date/heure → durée → statut → étiquettes → notes.
- Sélection du client par recherche asynchrone, jamais un `<select>` de 20 entrées.
- Valeurs par défaut intelligentes : date/heure = maintenant, agent = utilisateur connecté.
- Erreurs affichées sous le champ concerné, focus déplacé sur le premier champ en erreur.
- Un bouton primaire unique par écran.

## Dashboard

- Les indicateurs chiffrés en haut (volume, durée moyenne, taux de résolution), les graphiques en dessous, le détail encore en dessous.
- Un graphique répond à une question ; s'il n'en répond à aucune, il dégage.
- Chaque graphique a un titre explicite, des axes légendés et une alternative textuelle ou un tableau accessible.
- Sélecteur de période commun à tout l'écran.

## Accessibilité — le minimum non négociable

- Contraste AA (4,5:1 sur le texte courant).
- Tout ce qui est cliquable est atteignable au clavier, avec un anneau de focus visible.
- `<label>` associé à chaque champ ; un placeholder n'est pas un label.
- Un seul `<h1>` par page, hiérarchie de titres continue.
- Icône seule ⇒ `aria-label`.
- Message de succès/erreur dans une région `aria-live`.

## Responsive

Mobile d'abord sur les écrans de saisie. En dessous de `md`, le tableau devient une liste de cartes plutôt qu'un scroll horizontal. Aucun scroll horizontal sur le `body`.

## Cohérence

Utiliser les composants `components/ui/` (shadcn-vue) fournis avant d'en écrire un. Une seule échelle d'espacement, une seule palette, un seul style de bouton par niveau d'importance.
