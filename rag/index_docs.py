import argparse, glob, os, uuid
import ollama
from qdrant_client import QdrantClient
from qdrant_client.models import Distance, PointStruct, VectorParams

CHUNK, OVERLAP = 800, 120   # en caractères : ~200 tokens, ~15 % de recouvrement

def title_of(text, fallback):
    for line in text.splitlines():
        if line.startswith("# "):
            return line[2:].strip()
    return fallback

def source_type_of(name):
    if name.startswith("wiki-"): return "wiki"
    if name.startswith("README"): return "code"
    return "doc"

p = argparse.ArgumentParser()
p.add_argument("--source", default="database/seeds/documents")
p.add_argument("--collection", default="compas_chunks")
args = p.parse_args()

client = QdrantClient(url="http://localhost:6333")
if client.collection_exists(args.collection):
    client.delete_collection(args.collection)   # réindexation complète à chaque lancement
client.create_collection(args.collection,
    vectors_config=VectorParams(size=768, distance=Distance.COSINE))   # 768 = nomic-embed-text

for file in sorted(glob.glob(os.path.join(args.source, "*.md"))):
    name = os.path.basename(file)[:-3]
    text = open(file, encoding="utf-8").read()
    starts = range(0, len(text), CHUNK - OVERLAP)
    chunks = [text[s:s + CHUNK] for s in starts]
    vectors = ollama.embed(model="nomic-embed-text", input=chunks)["embeddings"]
    client.upsert(args.collection, points=[
        PointStruct(id=str(uuid.uuid5(uuid.NAMESPACE_URL, f"{name}:{s}")), vector=v, payload={
            "path": f"seeds/documents/{name}.md",   # même valeur que Document.path
            "title": title_of(text, name),
            "source_type": source_type_of(name),
            "line": text.count("\n", 0, s) + 1,     # pour citer chemin:ligne, comme COMPAS
            "text": c,
        }) for s, c, v in zip(starts, chunks, vectors)])
    print(f"{name}: {len(chunks)} chunks")