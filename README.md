# setup-phpmyadmin

Automate the process of downloading, extracting, and setting up the latest phpMyAdmin release using a simple PHP script.

## Features

- Automatically downloads the latest phpMyAdmin ZIP archive from the official source
- Extracts the contents safely into a temporary directory
- Moves files into a clean `pma/` directory ready for use
- Cleans up temporary files and ZIP archives after installation
- Provides detailed CLI or web output with timestamps
- Error handling and logging for easier debugging

## Requirements

- PHP 7.2 or higher with `zip` extension enabled
- `allow_url_fopen` enabled to download files via `file_get_contents`
- Writable web directory to create `pma/` and temporary folders

## Usage

### Via CLI

Run the script from the command line:

```bash
php setup-phpmyadmin.php
```

### Via Web Browser

Upload the script to your web server and access it through your browser. Make sure the directory permissions allow file creation and deletion.

## Configuration

You can customize these variables at the top of `setup-phpmyadmin.php`:

- `$downloadUrl` — URL to the latest phpMyAdmin ZIP file (default points to official latest release)
- `$pmaDir` — target directory where phpMyAdmin files will be placed (default: pma)
- `$tempDir` — temporary extraction directory (default: temp_pma_extract)

## How It Works

- Downloads the latest phpMyAdmin ZIP archive.
- Creates necessary directories for extraction and final files.
- Extracts the ZIP contents to a temporary directory.
- Moves extracted files from temporary directory to the final pma/ directory.
- Deletes temporary extraction folders and ZIP files.
- Logs progress and errors to phpmyadmin_setup_errors.log.

## Error Handling

Script displays errors on screen and logs detailed messages in phpmyadmin_setup_errors.log.

If download or extraction fails, the script stops and outputs an error message.

## License

This project is licensed under the MIT License — see the [LICENSE](LICENSE) file for details.

## Contribution

Contributions and improvements are welcome! Feel free to open issues or pull requests.

Made with ❤️ by Max Base.
