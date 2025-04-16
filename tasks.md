
- [ ] merge old tasks
- [ ] maybe improve file structure

- [x] verify we can use

  - ai said yes

  - multiple clients per page (SseMessageClient or AjaxMessageClient)
  - and a seperate MessageDisplay for each SseMessageClient or AjaxMessageClient 

- review placeholders: too much? enough?

  - ideally message could consist of multiple fields that is rendered inan user defined way
    - currently is definable => easy extension possible
      - maybe we don't really need the type field, defined by message

  - [ ] keep fields for sample add one but make dynamic

  - `{message}` - The message text
  - `{type}` - Message type (info, warning, danger, success)
  - `{id}` - Unique message ID
  - `{timestamp}` - Message timestamp

- Cleanup
  - [ ] replace old lib
  - [ ] clean kb (we don't need all old alternatives)
  - [ ] move 2 tasks below
  - [ ] add a text in dashb that we could use msgs now

old lib had

- [ ] prepend or append (currently left out)

- more logging options

  - [>] command line (maybe use a sep class for this, can't be mixed)
    - colors
  - [>] file (when used as UI) (use a sep class for this, or this is just the use yml file as ui task)


Alternatives
----------------------------------------------------------

- AI recommends

  - SSE (this might be the best if easy usage)
  - plain ajax solution (second best)
  - xhr more complicated for the features

- WebSockets are more for chat (2 way comm)

| Criteria                | XHR (AJAX) | SSE             | WebSockets         |
|-------------------------|------------|------------------|---------------------|
| Simple request/response | ✅ Best   | 🚫               | 🚫                   |
| One-way server → client | 🚫         | ✅ Best          | ✅                   |
| Two-way real-time (chat)| 🚫         | 🚫               | ✅ Best              |
| Auto-reconnect          | 🚫 Manual  | ✅ Built-in      | 🚫 Manual reconnect |
| Browser support         | ✅ All     | ✅ (except IE)   | ✅ All modern        |
| Resource efficiency     | ❌         | ✅ Light         | ✅ Efficient         |
| Message ordering        | ✅         | ✅ Ordered       | 🟡 Usually ordered   |
| Scalability (server)    | ✅ Easy    | ✅ Easier        | ❌ Harder (stateful) |


Removed from prompt
----------------------------------------------------------

- should work: Can be used Session based (with a logged in user), or within an app that has no Sessions
- maybe unneeded: Stop waiting for messges
  - Server sends a stop signal
  - add a timeout mechanism on the javascript side
