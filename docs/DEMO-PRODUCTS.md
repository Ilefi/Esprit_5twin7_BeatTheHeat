# Module 1 demo — step-by-step scenarios

Every advanced feature of **Produits & Catégories**, one scenario at a time: what to open, what to click, what you should see.
How each feature works is explained in `docs/INNOVATION-PRODUCTS.md`.

## 0. Before you start

1. Start the app (two terminals):
   ```
   php artisan serve
   npm run dev
   ```
2. (Optional) Reset the demo data: `php artisan migrate:fresh --seed`. This also removes barcodes you added by hand.
3. For the AI scenarios (8 and 9) you need a **Gemini key** (free):
   1. Go to https://aistudio.google.com/apikey, sign in with a Google account, click **Create API key**, copy it.
   2. In `.env`, set `GEMINI_API_KEY=your-key` (leave `GEMINI_MODEL=gemini-3.8-flash`).
   3. Stop and restart `php artisan serve`.
4. Scenarios 4, 5, 6, 8 and 9 call online APIs (Open Food Facts, Gemini): you need internet.

Accounts (password `password`): **admin@nutritrace.tn** (admin pages), **consumer@nutritrace.tn** (normal client). Public pages work with any account or none.

| # | Feature | Where | Account | Internet |
|---|---|---|---|---|
| 1 | Smart alternatives + comparison | `/produits/amandes-sfax` | any | no |
| 2 | Admin controls the alternatives (substitutes) | `/admin/categories` | admin | no |
| 3 | Ranking inside the category | `/produits/huile-olive-sfax` | any | no |
| 4 | World comparison (Open Food Facts) | `/produits/miel-thym-zaghouan` | any | yes |
| 5 | Barcode import | `/admin/produits/create` | admin | yes |
| 6 | Nutrition tab from a barcode | `/admin/produits` → public page | admin | yes |
| 7 | Category eco-score statistics | `/admin/categories` | admin | no |
| 8 | AI product sheet writer (Gemini) | `/admin/produits/create` | admin | yes + key |
| 9 | AI category assistant (Gemini) | `/admin/categories` | admin | yes + key |

---

## Scenario 1 — Smart alternatives ("Meilleures alternatives")

**Goal:** NutriTrace suggests greener products to replace the one you are looking at.

1. Open **`/produits/amandes-sfax`** (almonds, eco-score **D**).
2. Scroll down to the section **"Meilleures alternatives"** (small label "Recommandation durable"). Don't confuse it with **"Produits similaires"** just below.
3. You see 3 cards: **Oranges maltaises de Nabeul**, **Figues fraîches de l'oasis**, **Dattes Deglet Nour de Tozeur**.
4. On each card, check:
   - the score out of 100 (top right of the image), e.g. **76 / 100**;
   - the reasons, e.g. "Éco-score A au lieu de D", "−81 % de CO₂", "215 km de transport en moins".
5. Click **Comparer** on the oranges card → the comparison page `/empreinte/comparer` opens with the almonds and the oranges side by side.

**What it proves:** an internal recommendation algorithm (no API) that combines the eco-score (Module 3), ratings (Module 4), certifications, category and region.

---

## Scenario 2 — The admin controls the alternatives

**Goal:** the alternatives depend on the "substitute categories" set by the admin.

1. Log in as **admin@nutritrace.tn**.
2. Open **`/admin/categories`**. On the **Fruits secs** card, the line **"Remplaçable par"** shows *Fruits, Produits de la ruche*.
3. Click the **pencil ✏️** on the **Fruits secs** card.
4. In the window, under **"Catégories de remplacement"**, **uncheck "Fruits"**, then click **Enregistrer**.
5. Open **`/produits/amandes-sfax`** again (refresh). In "Meilleures alternatives", the 3 fruits are gone: only **Miel de thym du Djebel Zaghouan** remains.
6. Open the **Fruits** card's pencil: "Fruits secs" is no longer checked there either (the link works both ways).
7. **Restore:** edit **Fruits secs** again, **check "Fruits"**, **Enregistrer**. The 3 fruits come back on the almonds page.

---

## Scenario 3 — Ranking inside the category

The ranking needs at least 2 rated products in the category (the honey is alone in "Produits de la ruche", so it has none).

1. Open **`/produits/huile-olive-sfax`**.
2. Under the product title, read **"Plus durable que 50 % des produits de la catégorie"**.
3. Click the tab **"Nutrition & repères"** → right column **"Repères de la catégorie"**, first card: **"2e sur 3"**, the average eco-score of "Huiles & condiments" and the A→E bar.
4. Compare with **`/produits/oranges-maltaises-nabeul`**: **"Plus durable que 100 %"**, 1er sur 3 (the best of its category, which is also why it has no "Meilleures alternatives").

---

## Scenario 4 — World comparison (Open Food Facts API)

1. Still on **`/produits/miel-thym-zaghouan`**, tab **"Nutrition & repères"**.
2. In "Repères de la catégorie", the card **"Dans le monde, 19 751 produits référencés par Open Food Facts (en:honeys)"** shows:
   - **Green-Score moyen** and **Nutri-Score moyen** of honeys worldwide, with their bars;
   - a sentence like "Éco-score A : mieux que la moyenne mondiale de sa catégorie (B)".
3. Click the **`en:honeys`** link → the category page on openfoodfacts.org opens.

---

## Scenario 5 — Barcode import (admin)

1. As admin, open **`/admin/produits/create`**.
2. In the first card **"Import Open Food Facts"**, type **`3017620422003`** in **"Code-barres (EAN)"**.
3. Click the **download button** next to the field (or press Enter).
4. You see:
   - the empty fields filled: **Nom du produit** ("Nutella"), **Format**, **Description**, **Composition**;
   - a green box: product name, brand, Nutri-Score, NOVA group, allergens, and a **"Voir sur Open Food Facts"** link.
5. Type something in "Nom du produit" first and import again: your text is **kept** (only empty fields are filled).
6. Don't save (it would create a "Nutella" product); just leave the page.

---

## Scenario 6 — Nutrition tab from a barcode

1. As admin, open **`/admin/produits`** and click **edit** on **Miel de thym du Djebel Zaghouan**.
2. In **"Code-barres (EAN)"**, type **`5200111920616`** (a real honey, "Miel de pin" on Open Food Facts).
3. Click **Enregistrer** at the bottom right. You should land on the product's admin page with a green "mis à jour" message.
   - If you stay on the form, a red message is under a field. **"Un produit utilise déjà ce code-barres"** means another product already has it: a barcode belongs to one product only.
4. Open **`/produits/miel-thym-zaghouan`** → tab **"Nutrition & repères"**. The left column **"Fiche nutritionnelle"** now shows: **Nutri-Score**, **NOVA** group, a table of nutrients per 100 g, allergens, and a link to the Open Food Facts page.

---

## Scenario 7 — Category statistics (admin)

1. Open **`/admin/categories`**.
2. Each card shows: number of products, **Éco-score moyen**, the colored **A→E distribution bar**, **"Remplaçable par"** and the **Open Food Facts** tag (click it to open the category on openfoodfacts.org).

---

## Scenario 8 — AI product sheet writer (Gemini)

**Goal:** Gemini writes the product description and composition from a few fields.

**8a. Generate a new sheet**
1. As admin, open **`/admin/produits/create`**.
2. In the card **"Informations générales"**, fill only:
   - **Nom du produit**: `Miel de romarin`
   - **Catégorie**: *Produits de la ruche*
   - **Région d'origine**: `Zaghouan`
   - **Format**: `Pot verre 250 g`
3. In **"Certifications"**, check **Produit local**.
4. Click the yellow button **"Rédiger avec l'IA"** (top right of "Informations générales"). It shows "Rédaction…" for a few seconds.
5. **Description** and **Composition** fill in. A yellow box says "Brouillon rédigé par Gemini — Rempli : description et composition. Relisez et corrigez avant d'enregistrer."
6. Read the text: it mentions Zaghouan and the local label, and no label you didn't check (Gemini is told not to invent certifications).

**8b. Your text is never overwritten without asking**
1. Change a word in the Description, then click **"Rédiger avec l'IA"** again.
2. Your text stays. The yellow box shows Gemini's proposal in a quote, with a button **"Remplacer mes textes par la proposition"**.
3. Click it → the proposal replaces your description.

**8c. Anti-greenwashing warning**
If Gemini's text contains an unproven environmental claim ("zéro impact", "écologique", "durable"…), the box shows in orange: **"Allégation à justifier ou à retirer : « … »"**. Remove the word from the description before saving.

**8d. Use the barcode as extra context**
1. Reload `/admin/produits/create`, import **`5200111920616`** in "Import Open Food Facts" (scenario 5), then empty the **Description**.
2. Click **"Rédiger avec l'IA"**: Gemini also uses the brand, ingredients and allergens from Open Food Facts.

**8e. Error messages**
- Click "Rédiger avec l'IA" with an empty name → red message "Saisissez au moins le nom du produit…".
- Without a key in `.env` → red message "L'IA n'est pas activée : ajoutez GEMINI_API_KEY…".

Nothing is saved until you click **Créer le produit** (an image is required to create a product).

---

## Scenario 9 — AI category assistant (Gemini)

**Goal:** Gemini suggests the icon, the Open Food Facts tag and the substitute categories of a category, and the tag is checked against Open Food Facts.

**9a. New category**
1. As admin, open **`/admin/categories`** and click **"Nouvelle catégorie"** (top right).
2. Type the **Nom**: `Confitures`. Leave **Icône** and **Tag Open Food Facts** empty.
3. Click the yellow button **"Suggérer avec l'IA"**. It shows "Analyse…".
4. A yellow box appears, for example:
   - a summary sentence;
   - **Icône**: the jar icon → the **Icône** field is now set to "Bocal";
   - **Tag Open Food Facts**: `en:jams` **· vérifié, N produits** (green) → the tag field is filled. If it says "introuvable sur Open Food Facts, à vérifier" (orange), Gemini proposed a tag that doesn't exist: correct it;
   - **Remplaçable par** *Produits de la ruche* / *Fruits* with a reason for each → those boxes are now **checked** under "Catégories de remplacement".
5. Click **Créer**. The new card appears with its tag and substitutes; the substitutes also list "Confitures" (link both ways).

**9b. Existing category**
1. Click the **pencil ✏️** on **Fruits secs**, then **"Suggérer avec l'IA"**.
2. The icon and tag are already set, so they are **not changed**; suggested substitutes are checked, existing ones stay checked.
3. To take every suggestion, click **"Appliquer toutes les suggestions"** (overwrites icon and tag). Click **Annuler** to discard, or **Enregistrer** to keep.

**9c. Error message**
Click "Suggérer avec l'IA" with an empty name → "Saisissez d'abord le nom de la catégorie."

Gemini's answers vary a little from one click to the next: that's normal for an AI. The server only keeps an icon from the allowed list, a well-formed tag, and categories that really exist.

---

## Automated tests

```
php artisan test --filter=ProductRecommendationTest   # scenarios 1–7 (Open Food Facts faked)
php artisan test --filter=CatalogAssistantTest        # scenarios 8–9 (Gemini faked)
```
