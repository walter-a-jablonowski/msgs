
Make a script that runs PHP via ajax and display progress messages as long as the script runs.

- Usable in multiple apps
- Integreation in an app as easy as possible
- User defined message format per app
- Messages could be displayed anywhere in the page, also usable in a BS modal
  - ideally if the modal is closed and opened again the messages should be visible again
- If we leave the page that show the messages and go back to it, the messages should ideally still be visible (if possible)
- Mulitple seperate proccesses may be started on a page that display messages in different locations on the page

### Technology alternatives

Make 2 implementation variants in seperate sub folders:

- SSE solution
- plain ajax solution (most likely second best)
  - store messages in a file system folder and only get them via ajax
  - because we can have multiple messages this may require unique ids

### Avoid

- jquery, third party libraries
- also avoid: local storage

### Sample page

For each implementation variant make a sample html page that has

- BS 5.3
- message sample in the main page
- message sample in a modal

make it look nice
