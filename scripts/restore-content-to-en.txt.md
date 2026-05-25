# Restore content after .txt → .en.txt rename

## What went wrong

When preparing for multi-language, we renamed page content files from `.txt` to `.en.txt`. Where both `file.txt` and `file.en.txt` existed, we **deleted** `file.txt` and kept `file.en.txt`. In many cases the **real content** was in `file.txt` and `file.en.txt` was a stub (e.g. only `Uuid:`), so that content was lost.

## How to recover

1. **Restore the deleted `.txt` files** from a backup (Time Machine, backup drive, or any copy of the `content/` folder from before the rename).
2. **Copy content from `.txt` into `.en.txt`** so that `.en.txt` gets the full content, then remove the duplicate `.txt`.

### Option A: Restore from backup, then run the script

If you have a backup of the `content/` folder (e.g. `content_backup/` or a Time Machine snapshot):

1. Copy the **deleted** `.txt` files from the backup back into your current `content/` tree (same paths as before). Do **not** overwrite existing `.en.txt` files when restoring.
2. From the project root, run:

   ```bash
   bash scripts/restore-content-to-en.sh
   ```

   This script finds every page content `.txt` (excluding media `.jpg.txt` etc.), **overwrites** the corresponding `.en.txt` with the content of the `.txt` file, then deletes the `.txt` file. So after this, only `.en.txt` remains and it has the restored content.

### Option B: Restore from backup manually

If you prefer to do it by hand:

1. Restore the deleted `.txt` files from backup into `content/`.
2. For each restored `path/file.txt`:
   - Copy the full content of `path/file.txt` into `path/file.en.txt` (overwrite `file.en.txt`).
   - Delete `path/file.txt`.

### Files that were removed (content was in .txt, we kept stub .en.txt)

- `content/home/home.txt`
- `content/3_fabulatoire/3_research-residency-arctic-futures/fabulatoire-item.txt`
- `content/4_agenda/agenda.txt`
- `content/4_agenda/1_sample-event/event.txt` (this one was renamed, not removed – only if you had both)
- `content/2_productions/productions.txt`
- `content/2_productions/7_after-the-walls-utopia/production.txt`
- `content/2_productions/13_die-anderen/production.txt`
- `content/2_productions/9_habituation/production.txt`
- `content/2_productions/5_que-puis-je-faire-pour-vous/production.txt`
- `content/2_productions/3_tristesses/production.txt`
- `content/2_productions/4_still-too-sad-to-tell-you/production.txt`
- `content/2_productions/10_self-service/production.txt`
- `content/2_productions/11_hansel-and-gretel/production.txt`
- `content/2_productions/2_arctic/production.txt`
- `content/2_productions/12_zai-zai-zai-zai/production.txt`
- `content/2_productions/8_michel-dupont/production.txt`
- `content/2_productions/6_looking-for-dystopia/production.txt`
- `content/6_contact/contact.txt`
- `content/error/error.txt`

If you don’t have a backup, the only way to get this content back is to re-enter it in the Kirby Panel (or from any other copy you have).
