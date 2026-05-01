#!/usr/bin/env bash
set -euo pipefail

usage() {
  cat <<'EOF'
Usage:
  scripts/docker-build-push.sh --user <dockerhub_user> --image <image_name> --tag <version> [options]

Required:
  --user        Docker Hub username or organization
  --image       Image name (without registry/user prefix)
  --tag         Version tag (e.g. 7.3.2)

Options:
  --platforms   Target platforms for buildx (default: linux/amd64)
  --dockerfile  Dockerfile path relative to repo root (default: docker/Dockerfile.production)
  --no-latest   Do not push the latest tag
  --help        Show this help

Example:
  scripts/docker-build-push.sh \
    --user mydockerhubuser \
    --image churchcrm-custom \
    --tag 7.3.2 \
    --platforms linux/amd64,linux/arm64
EOF
}

DOCKERHUB_USER=""
IMAGE_NAME=""
IMAGE_TAG=""
PLATFORMS="linux/amd64"
DOCKERFILE_REL="docker/Dockerfile.production"
PUSH_LATEST=true

while [[ $# -gt 0 ]]; do
  case "$1" in
    --user)
      DOCKERHUB_USER="${2:-}"
      shift 2
      ;;
    --image)
      IMAGE_NAME="${2:-}"
      shift 2
      ;;
    --tag)
      IMAGE_TAG="${2:-}"
      shift 2
      ;;
    --platforms)
      PLATFORMS="${2:-}"
      shift 2
      ;;
    --dockerfile)
      DOCKERFILE_REL="${2:-}"
      shift 2
      ;;
    --no-latest)
      PUSH_LATEST=false
      shift
      ;;
    --help|-h)
      usage
      exit 0
      ;;
    *)
      echo "Unknown option: $1" >&2
      usage
      exit 1
      ;;
  esac
done

if [[ -z "${DOCKERHUB_USER}" || -z "${IMAGE_NAME}" || -z "${IMAGE_TAG}" ]]; then
  echo "Missing required arguments." >&2
  usage
  exit 1
fi

if ! command -v docker >/dev/null 2>&1; then
  echo "Docker CLI is required." >&2
  exit 1
fi

if ! docker buildx version >/dev/null 2>&1; then
  echo "docker buildx is required." >&2
  exit 1
fi

DOCKER_USERNAME="$(docker info --format 'ghisadmin' 2>/dev/null || true)"
if [[ -z "${DOCKER_USERNAME}" ]]; then
  echo "Not logged in to Docker Hub. Run: docker login" >&2
  exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
DOCKERFILE_PATH="${PROJECT_ROOT}/${DOCKERFILE_REL}"

if [[ ! -f "${DOCKERFILE_PATH}" ]]; then
  echo "Dockerfile not found: ${DOCKERFILE_PATH}" >&2
  exit 1
fi

FULL_IMAGE="${DOCKERHUB_USER}/${IMAGE_NAME}"
BUILDER_NAME="churchcrm-publisher"

if ! docker buildx inspect "${BUILDER_NAME}" >/dev/null 2>&1; then
  docker buildx create --name "${BUILDER_NAME}" --driver docker-container --use >/dev/null
else
  docker buildx use "${BUILDER_NAME}" >/dev/null
fi

docker buildx inspect --bootstrap >/dev/null

TAG_ARGS=(--tag "${FULL_IMAGE}:${IMAGE_TAG}")
if [[ "${PUSH_LATEST}" == "true" ]]; then
  TAG_ARGS+=(--tag "${FULL_IMAGE}:latest")
fi

echo "Building and pushing ${FULL_IMAGE}:${IMAGE_TAG}"
if [[ "${PUSH_LATEST}" == "true" ]]; then
  echo "Also pushing ${FULL_IMAGE}:latest"
fi
echo "Platforms: ${PLATFORMS}"
echo "Dockerfile: ${DOCKERFILE_REL}"

docker buildx build \
  --platform "${PLATFORMS}" \
  --file "${DOCKERFILE_PATH}" \
  "${TAG_ARGS[@]}" \
  --push \
  "${PROJECT_ROOT}"

echo "Done."
