const express = require("express");
const cors = require('cors');
const bodyParser = require('body-parser');
const app = express();
app.use(cors());
app.use(bodyParser.json());
const port = process.env.PORT || 8081;

const themes = ["classy", "kendall", "ravgift", "standard-resume", "elegant"];

app.get("/", (req, res) => {
  res.send("json resume render api");
});

app.get("/themes", (req, res) => {
  res.json(themes);
});

app.post("/theme/:theme", (req, res) => {
  const json = req.body.resume;
  //const json = require('./data.json')
  const theme = req.params.theme;
  console.log(json)
  if (! theme in themes) {
    return res.status(400).json({ error: "Theme not found" });
  };
  //const render = require(`jsonresume-theme-${theme}`).render;
  const render = require(`jsonresume-theme-${theme}`).render;
 
  res.send(render(json));
});

app.listen(port, () => {
  console.log("Running on port", port);
});